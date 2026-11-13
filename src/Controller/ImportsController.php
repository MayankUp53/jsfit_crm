<?php

declare(strict_types=1);

namespace App\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }
        $sheet->freezePane('A2');
        foreach (range('A', 'Y') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $this->response = $this->response
            ->withType('xlsx')
            ->withDownload('顧客取込雛形.xlsx');
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $this->response = $this->response->withStringBody($content);
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

    try {
        $spreadsheet = IOFactory::load($filePath);
    } catch (\Throwable $e) {
        $this->Flash->error(__('取込ファイルを読み込めませんでした。ファイル形式または内容を確認してください。'));
        return $this->redirect(['controller' => 'Settings', 'action' => 'index']);
    }
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
    $header = array_map(static function ($value): string {
        return trim((string)preg_replace('/^\xEF\xBB\xBF/u', '', $value ?? ''));
    }, $rows[0]);
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
    $legacyBlankTemplate = count($header) === count($template)
        && count(array_filter($header, static fn($value) => trim((string)$value) !== '')) === 0;
    if ($header !== $template && !$legacyBlankTemplate) {
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
        $importedPending = false;
        $updatedPending = false;
        $reactivatedPending = false;

        $row = array_map(function ($v) { return trim((string)($v ?? '')); }, $row);
        $row = array_pad($row, 25, '');
        [
            $clientCode,
            $clientBranchCode,
            $clientName,
            $clientBranchName,
            $addr1,
            $addr2,
            $addr3,
            $tel,
            $fax,
            $tel2,
            $incharge1Name,
            $incharge1Phone,
            $incharge2Name,
            $incharge2Phone,
            $incharge3Name,
            $incharge3Phone,
            $incharge4Name,
            $incharge4Phone,
            $sectionName,
            $salesPerson,
            $deadline,
            $notes1,
            $notes2,
            $notes3,
            $notes4,
        ] = $row;

        if (count(array_filter($row, static fn($value) => $value !== '')) === 0) {
            continue;
        }
        if (empty($clientCode)) {
            $failed++;
            continue;
        }

        $normalizedTel = $this->cleanPhone($tel) ?? '';

        $entity = $this->Customers->find()
            ->where(['OR' => [
                ['client_cd' => $clientCode],
                ['client_code' => $clientCode],
            ]])
            ->first();

        $data = [
            'client_code' => $clientCode,
            'client_cd' => $clientCode,
            'client_branch_code' => $clientBranchCode,
            'client_name' => $clientName,
            'client_branch_name' => $clientBranchName,
            'company_name' => $clientName,
            'company_branch_name' => $clientBranchName,
            'name' => $clientName,
            'addr1' => $addr1,
            'addr2' => $addr2,
            'addr3' => $addr3,
            'tel' => $normalizedTel,
            'tel_only' => $normalizedTel,
            'phone' => $normalizedTel,
            'phone_only' => $normalizedTel,
            'fax' => $fax,
            'tel2' => preg_replace('/[^0-9]/', '', $tel2),
            'incharge1_name' => $incharge1Name,
            'incharge1_phone' => preg_replace('/[^0-9]/', '', $incharge1Phone),
            'incharge2_name' => $incharge2Name,
            'incharge2_phone' => preg_replace('/[^0-9]/', '', $incharge2Phone),
            'incharge3_name' => $incharge3Name,
            'incharge3_phone' => preg_replace('/[^0-9]/', '', $incharge3Phone),
            'incharge4_name' => $incharge4Name,
            'incharge4_phone' => preg_replace('/[^0-9]/', '', $incharge4Phone),
            'section_name' => $sectionName,
            'sales_person' => $salesPerson,
            'deadline' => $deadline,
            'notes1' => $notes1,
            'notes2' => $notes2,
            'notes3' => $notes3,
            'notes4' => $notes4,
            'updated' => $now,
            'updated_user_id' => $userId,
        ];

        if ($entity) {
            if (!empty($entity->deleted)) {
                $reactivatedPending = true;
            } else {
                $updatedPending = true;
            }
            $data['deleted'] = null;
            $data['deleted_user_id'] = null;
        } else {
            $entity = $this->Customers->newEmptyEntity();
            $data['name'] = $clientName;
            $data['company_name'] = $clientName;
            $data['created'] = $now;
            $data['created_user_id'] = $userId;
            $importedPending = true;
        }

        $entity = $this->Customers->patchEntity($entity, $data);

        if (!$this->Customers->save($entity)) {
            $failed++;

            foreach ($entity->getErrors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = $field . ': ' . $message;
                }
            }
        } elseif (!empty($reactivatedPending)) {
            $reactivated++;
        } elseif (!empty($updatedPending)) {
            $updated++;
        } elseif (!empty($importedPending)) {
            $imported++;
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
            '得意先支店CD',
            '得意先名',
            '得意先支店名',
            '住所1',
            '住所2',
            '住所3',
            'TEL',
            'FAX',
            'サブ電話番号1',
            '顧客の担当者1',
            '顧客の担当者1電話',
            '顧客の担当者2',
            '顧客の担当者2電話',
            '顧客の担当者3',
            '顧客の担当者3電話',
            '顧客の担当者4',
            '顧客の担当者4電話',
            '所属名',
            '営業担当者名',
            '締日',
            '備考1',
            '備考2',
            '備考3',
            '備考4',
        ];
    }
}
