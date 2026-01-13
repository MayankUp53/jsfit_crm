<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Imports Controller
 *
 * Customer data import.
 */
class ImportsController extends AppController
{
    protected $Customers;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');
        $this->Customers = $this->fetchTable('Customers');
    }

    public function index()
    {
        $warning = $this->CallbackNotification->getNumOfWarning();
        $this->set('importNote', __('CSV形式でアップロードしてください。1行目はヘッダーとして無視されます。'));
        $this->set(compact('warning'));
    }

  /**
 * Download import template
 */
public function template()
{
    $headers = [
        '得意先CD',
        '会社・支店名',
        '氏名',
        '番号種別',
        'Tel',
        'Fax',
        'メールアドレス',
        '登録日',
        'メモ'
    ];

    $filename = 'import_template_customers.csv';
    $this->response = $this->response
        ->withType('csv')
        ->withDownload($filename);

    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    fclose($out);

    return $this->response;
}


    /**
     * Basic CSV import for customers.
     */
    
public function import()
{
    if (!$this->request->is('post')) {
        return $this->redirect(['action' => 'index']);
    }

    $file = $this->request->getData('result');
    if (empty($file) || $file->getError() !== UPLOAD_ERR_OK) {
        $this->Flash->error(__('ファイルのアップロードに失敗しました。'));
        return $this->redirect(['action' => 'index']);
    }

    $handle = fopen($file->getStream()->getMetadata('uri'), 'r');
    if ($handle === false) {
        $this->Flash->error(__('ファイルを読み込めませんでした。'));
        return $this->redirect(['action' => 'index']);
    }

    // Skip header
    fgetcsv($handle);

    $imported = 0;
    $failed = 0;
    $errorMessages = [];

    $now = date('Y-m-d H:i:s');
    $currentUser = $this->getCurrentUser();
    $userId = $currentUser?->id ?? 0;

    while (($row = fgetcsv($handle)) !== false) {

        $row = array_map(function ($v) {
            if ($v === null) return null;
            $v = (string)$v;

            // remove UTF-8 BOM
            $v = preg_replace('/^\xEF\xBB\xBF/', '', $v);

            // convert Japanese encodings → UTF-8
            return mb_convert_encoding($v, 'UTF-8', 'UTF-8, SJIS-win, CP932, SJIS, EUC-JP');
        }, $row);

        // Column count check
        if (count($row) < 8) {
            $failed++;

            $key = 'column_count';
            if (!isset($errorMessages[$key])) {
                $errorMessages[$key] = 'CSVのカラム数が不足している行があります。';
            }
            continue;
        }

        [
            $clientCd,
            $companyName,
            $nameKj,
            $typeOfNumber,
            $tel,
            $fax,
            $email,
            $memo,
    
        ] = $row;

        $entity = $this->Customers->newEmptyEntity();

        $data = [
            'client_cd' => $clientCd,
            'company_name' => $companyName,
            'name' => $nameKj,
            'type_of_number' => $typeOfNumber,
            'tel' => $tel,
            'fax' => $fax,
            'email' => $email,
            'memo' => $memo,
            'zip_cd' => '',
            'addr1' => '',
            'disp_no' => date('ymdHis') . substr((string)microtime(true), -3),
            'created' => $now,
            'created_user_id' => $userId,
            'updated' => $now,
            'updated_user_id' => $userId,
        ];

        $entity = $this->Customers->patchEntity($entity, $data);

        if ($this->Customers->save($entity)) {
            $imported++;
        } else {
            $failed++;

            foreach ($entity->getErrors() as $field => $messages) {
                foreach ($messages as $message) {
                    $key = $field . ':' . $message;

                    if (!isset($errorMessages[$key])) {
                        $errorMessages[$key] = $message;
                    }
                }
            }
        }
    }

    fclose($handle);

    if (!empty($errorMessages)) {
        $this->Flash->error(
            __("取込エラー:\n") . implode("\n", $errorMessages)
        );
    }

    $summary = sprintf('取込完了: %d件 / 失敗: %d件', $imported, $failed);

    if ($imported > 0) {
        $this->Flash->success(__($summary));
        return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
    }

    $this->Flash->error(__('全件の取込に失敗しました。 ') . $summary);
    return $this->redirect(['controller' => 'Settings', 'action' => 'index']);
}

    /**
     * Remove hyphens/spaces from phone string.
     */
    protected function cleanPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
