<?php

declare(strict_types=1);

namespace App\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    $filePath = $file->getStream()->getMetadata('uri');
    $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));

    // allow all formats
    if (!in_array($extension, ['csv', 'xlsx', 'xls', 'ods'])) {
        $this->Flash->error(__('CSV / Excel / ODS ファイルのみ対応しています。'));
        return $this->redirect(['action' => 'index']);
    }

    // 🔥 LOAD FILE
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();

    // 🔥 IMPORTANT FIX: force NORMAL index array (0,1,2...)
    $rows = $sheet->toArray(null, true, true, false);

    if (empty($rows)) {
        $this->Flash->error(__('ファイルが空です。'));
        return $this->redirect(['action' => 'index']);
    }

    // =====================
    // HEADER (ROW 0 FIX)
    // =====================
    $header = array_map('trim', $rows[0]);
    $template = array_map('trim', $this->getCsvTemplate());

    // // DEBUG (your same style)
    // echo "<pre>";
    // echo "HEADER:\n";
    // print_r($header);

    // echo "\nTEMPLATE:\n";
    // print_r($template);
    // echo "</pre>";
    // die();

    // =====================
    // VALIDATION (same logic)
    // =====================
    if ($header !== $template) {
        $this->Flash->error(__('CSVのヘッダーが正しくありません。列の順番や名称を確認してください。'));
        return $this->redirect(['controller' => 'Settings', 'action' => 'index']);
    }

    // remove header
    array_shift($rows);

    // =====================
    // SAME VARIABLES (UNCHANGED)
    // =====================
    $imported = 0;
    $updated = 0;
    $reactivated = 0;
    $failed = 0;
    $errorMessages = [];

    $now = date('Y-m-d H:i:s');
    $userId = $this->getCurrentUser()?->id ?? 0;

    // =====================
    // LOOP (FIXED SAFE ROW HANDLING ONLY)
    // =====================
    foreach ($rows as $row) {

        $row = array_map('trim', $row);
        $row = array_pad($row, 8, '');

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
                    $errorMessages[] = $field . ': ' . $message;
                }
            }
        }
    }

    // =====================
    // SAME OUTPUT LOGIC
    // =====================
    if (!empty($errorMessages)) {
        $this->Flash->error("取込エラー:\n" . implode("\n", $errorMessages));
    }

    $summary = sprintf(
        '取込結果: 新規 %d件 / 更新 %d件 / 再有効化 %d件 / 失敗 %d件',
        $imported,
        $updated,
        $reactivated,
        $failed
    );

    $this->Flash->success($summary);
    return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
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
