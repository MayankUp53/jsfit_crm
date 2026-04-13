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
        $headers = $this->getCsvTemplate();

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

    // ✅ Read header
    $header = fgetcsv($handle);
    $template = $this->getCsvTemplate();

    // ✅ FIX: Remove BOM from first column
    if (isset($header[0])) {
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    }

    // ✅ FIX: Trim spaces
    $header = array_map('trim', $header);
    $template = array_map('trim', $template);

    // ✅ Compare header
    if ($header !== $template) {
        fclose($handle);
        $this->Flash->error(__('CSVのヘッダーが正しくありません。列の順番や名称を確認してください。'));
        return $this->redirect(['controller' => 'Settings', 'action' => 'index']);
    }

    $imported = 0;
    $updated = 0;
    $reactivated = 0;
    $failed = 0;
    $errorMessages = [];

    $now = date('Y-m-d H:i:s');
    $currentUser = $this->getCurrentUser();
    $userId = $currentUser?->id ?? 0;

    while (($row = fgetcsv($handle)) !== false) {

        $row = array_map(function ($v) {
            if ($v === null) return null;
            $v = (string)$v;

            // Remove BOM if present
            $v = preg_replace('/^\xEF\xBB\xBF/', '', $v);

            // Convert encoding
            return mb_convert_encoding($v, 'UTF-8', 'UTF-8, SJIS-win, CP932, SJIS, EUC-JP');
        }, $row);

        $row = array_map('trim', $row); // ✅ extra safety

        $row = array_pad($row, 8, '');

        if (count($row) < 8) {
            $failed++;
            $errorMessages['column_count'] = 'CSVのカラム数が不足している行があります。';
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

        if (empty($clientCd)) {
            $failed++;
            $errorMessages['client_cd_missing'] = 'client_cdが空の行があります。';
            continue;
        }

        $entity = $this->Customers->find()
            ->where(['client_cd' => $clientCd])
            ->first();

        if ($entity) {

            if (!empty($entity->deleted)) {
                $reactivated++;
            } else {
                $updated++;
            }

            $data = [
                'company_name' => $companyName,
                'name' => $nameKj,
                'type_of_number' => $typeOfNumber,
                'tel' => $tel,
                'fax' => $fax,
                'email' => $email,
                'memo' => $memo,
                'updated' => $now,
                'updated_user_id' => $userId,
                'deleted' => null,
                'deleted_user_id' => null,
            ];

        } else {

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

            $imported++;
        }

        $entity = $this->Customers->patchEntity($entity, $data);

        if (!$this->Customers->save($entity)) {
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

    $summary = sprintf(
        '取込結果: 新規 %d件 / 更新 %d件 / 再有効化 %d件 / 失敗 %d件',
        $imported,
        $updated,
        $reactivated,
        $failed
    );

    if (($imported + $updated + $reactivated) > 0) {
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
    function getCsvTemplate()
    {
        return [
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
    }
}
