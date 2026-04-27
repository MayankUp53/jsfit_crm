<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * MtPhonelists Controller
 *
 * Legacy phone book management.
 */
class MtPhonelistsController extends AppController
{
    protected $MtPhonelists;
    protected $Users;
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('App.CallbackNotification');

        $this->MtPhonelists = $this->fetchTable('MtPhonelists');
        $this->Users = $this->fetchTable('Users');
    }

   public function index()
{
    $mtPhonelists = $this->MtPhonelists->find()
        ->where(['deleted IS' => null])
        ->order(['disp_cd' => 'ASC'])
        ->toArray();

    $this->set(compact('mtPhonelists'));
    $this->setListValue();
}

 public function add()
{
    $entry = $this->MtPhonelists->newEmptyEntity();

    if ($this->request->is('post')) {

        $data = $this->request->getData();
        $now = date('Y-m-d H:i:s');
        $userId = $this->getCurrentUser()?->id ?? 0;

        // ❌ removed automatic disp_cd generation
        // $data['disp_cd'] = auto removed -> now user input only

        $data['created'] = $now;
        $data['created_user_id'] = $userId;
        $data['updated'] = $now;
        $data['updated_user_id'] = $userId;

        $entry = $this->MtPhonelists->patchEntity($entry, $data);

        if ($this->MtPhonelists->save($entry)) {
            $this->Flash->success(__('電話帳データの登録が完了しました。'));
            return $this->redirect(['action' => 'index']);
        }

        $this->Flash->error(__('電話帳データの登録に失敗しました。もう一度操作してください。'));
    }

    $this->set(compact('entry'));
    $this->setListValue();
}

  public function edit(?string $id = null)
{
    $entry = $this->MtPhonelists
        ->find()
        ->where([
            'id' => $id,
            'deleted IS' => null,
        ])
        ->firstOrFail();

    if ($this->request->is(['patch', 'post', 'put'])) {

        $data = $this->request->getData();
        $data['updated'] = date('Y-m-d H:i:s');
        $data['updated_user_id'] = $this->getCurrentUser()?->id ?? 0;

        $entry = $this->MtPhonelists->patchEntity($entry, $data);

        if ($this->MtPhonelists->save($entry)) {
            $this->Flash->success(__('電話帳データの更新が完了しました。'));
            return $this->redirect(['action' => 'index']);
        }

        $this->Flash->error(__('電話帳データの更新に失敗しました。もう一度操作してください。 または表示順序がすでに存在する'));
    }

    $this->set(compact('entry'));
    $this->setListValue();
}

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $entry = $this->MtPhonelists
            ->find()
            ->where([
                'id' => $id,
                'deleted IS' => null,
            ])
            ->firstOrFail();

        $entry->deleted = date('Y-m-d H:i:s');
        $entry->deleted_user_id = $this->getCurrentUser()?->id ?? 0;

        if ($this->MtPhonelists->save($entry, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('電話帳データの削除が完了しました。'));
        } else {
            $this->Flash->error(__('電話帳データの削除に失敗しました。もう一度操作してください。'));
        }

        return $this->redirect(['action' => 'index']);
    }



    public function updateOrder()
    {
        $this->request->allowMethod(['post']);

        try {
            // Read JSON payload from request body
            $data = json_decode($this->request->getBody()->getContents(), true);
            Log::debug('AJAX payload: ' . json_encode($data));

            if (empty($data['orderData'])) {
                throw new \Exception('No orderData received');
            }

            $mtPhonelistsTable = $this->getTableLocator()->get('MtPhonelists');

            // STEP 1: temporary negative disp_cd to avoid unique conflicts
            $entities = [];
            foreach ($data['orderData'] as $index => $item) {
                $entity = $mtPhonelistsTable->get($item['id']);
                $entity->disp_cd = - ($index + 1);
                $entities[] = $entity;
            }
            $mtPhonelistsTable->saveMany($entities, ['atomic' => true]);

            // STEP 2: assign final sequential numbers
            $entities = [];
            foreach ($data['orderData'] as $index => $item) {
                $entity = $mtPhonelistsTable->get($item['id']);
                $entity->disp_cd = $index + 1;
                $entities[] = $entity;
            }
            if (!$mtPhonelistsTable->saveMany($entities, ['atomic' => true])) {
                $errors = [];
                foreach ($entities as $e) {
                    $errors[$e->id] = $e->getErrors();
                }
                Log::error('SaveMany failed: ' . json_encode($errors));
                throw new \Exception('SaveMany failed, check logs');
            }

            // Success response
            $this->set(['message' => 'Order updated successfully']);
            $this->viewBuilder()->setOption('serialize', ['message']);
        } catch (\Exception $e) {
            Log::error('updateOrder failed: ' . $e->getMessage());

            $this->response = $this->response->withStatus(500);
            $this->set([
                'message' => 'Error saving order',
                'error' => $e->getMessage()
            ]);
            $this->viewBuilder()->setOption('serialize', ['message', 'error']);
        }
    }





    protected function setListValue(): void
    {
        $warning = $this->CallbackNotification->getNumOfWarning();
        $activeOpt = ['0' => '無効', '1' => '有効'];
        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['deleted IS' => null])
            ->orderBy(['id' => 'ASC'])
            ->toArray();

        $this->set(compact('warning', 'activeOpt', 'opeOpt'));
    }

    

public function template()
{
    // headers for import file
    $headers = [
        '得意先CD',
        '名称',
        '電話番号'
    ];

    $filename = 'phone_import_template.csv';

    $this->response = $this->response
        ->withType('csv')
        ->withDownload($filename);

    $out = fopen('php://output', 'w');

    // header row
    fputcsv($out, $headers);

    // optional sample row
    fputcsv($out, ['CUST001', '山田太郎', '09012345678']);

    fclose($out);

    return $this->response;
}
// public function import()
// {
//     if (!$this->request->is('post')) {
//         return $this->redirect(['action' => 'index']);
//     }

//     $file = $this->request->getData('result');

//     if (empty($file) || $file->getError() !== UPLOAD_ERR_OK) {
//         $this->Flash->error('ファイルのアップロードに失敗しました。');
//         return $this->redirect(['action' => 'index']);
//     }

//     $filePath = $file->getStream()->getMetadata('uri');

//     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
//     $sheet = $spreadsheet->getActiveSheet();
//     $rows = $sheet->toArray(null, true, true, false);

//     if (empty($rows)) {
//         $this->Flash->error('ファイルが空です。');
//         return $this->redirect(['action' => 'index']);
//     }

//     // HEADER CHECK
//     $header = array_map('trim', $rows[0]);
//     $template = ['名称', '電話番号'];

//     if ($header !== $template) {
//         $this->Flash->error('ヘッダーが正しくありません。');
//         return $this->redirect(['action' => 'index']);
//     }

//     array_shift($rows);

//     $table = $this->MtPhonelists;

//     $imported = 0;   // success
//     $skipped = 0;    // duplicate
//     $failed = 0;     // error
//     $errorMessages = [];

//     $now = date('Y-m-d H:i:s');
//     $userId = $this->getCurrentUser()?->id ?? 0;

//     $maxDisp = $table->find()
//         ->select(['max_disp' => $table->find()->func()->max('disp_cd')])
//         ->first();

//     $nextDisp = (int)($maxDisp?->max_disp ?? 0);

//     // LOOP
//     foreach ($rows as $index => $row) {

//         $row = array_map(fn($v) => trim($v ?? ''), $row);
//         $row = array_pad($row, 2, '');

//         [$name, $phone] = $row;

//         $phone = preg_replace('/[^0-9]/', '', $phone);

//         if (empty($name) || empty($phone)) {
//             $failed++;
//             $errorMessages[] = "行 " . ($index + 2) . ": 名称または電話番号が空です";
//             continue;
//         }

//         // duplicate check
//         if ($table->exists(['phone' => $phone])) {
//             $skipped++;
//             continue;
//         }

//         $nextDisp++;

//         $entity = $table->newEmptyEntity();

//         $data = [
//             'disp_cd' => $nextDisp,
//             'item_name' => $name,
//             'phone' => $phone,
//             'active_flg' => 1,
//             'created' => $now,
//             'updated' => $now,
//             'created_user_id' => $userId,
//             'updated_user_id' => $userId,
//         ];

//         $entity = $table->patchEntity($entity, $data);

//         if ($table->save($entity)) {
//             $imported++;
//         } else {
//             $failed++;
//             foreach ($entity->getErrors() as $field => $msgs) {
//                 foreach ($msgs as $msg) {
//                     $errorMessages[] = "行 " . ($index + 2) . ": " . $field . " - " . $msg;
//                 }
//             }
//         }
//     }

//     // =========================
//     // FINAL FLASH MESSAGE (JAPANESE)
//     // =========================

//     $this->Flash->success(
//         "取込結果: 成功 {$imported}件 / 重複スキップ {$skipped}件 / エラー {$failed}件"
//     );

//     if (!empty($errorMessages)) {
//         $this->Flash->error(
//             "エラー詳細（" . count($errorMessages) . "件）<br>" .
//             implode("<br>", $errorMessages)
//         );
//     }

//     return $this->redirect(['action' => 'index']);
// }



public function import()
{
    if (!$this->request->is('post')) {
        return $this->redirect(['action' => 'index']);
    }

    set_time_limit(0);
    ini_set('memory_limit', '1024M');

    $file = $this->request->getData('result');

    if (empty($file) || $file->getError() !== UPLOAD_ERR_OK) {
        $this->Flash->error('ファイルのアップロードに失敗しました。');
        return $this->redirect(['action' => 'index']);
    }

    $filePath = $file->getStream()->getMetadata('uri');

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
    } catch (\Exception $e) {
        $this->Flash->error('ファイルの読み込みに失敗しました。');
        return $this->redirect(['action' => 'index']);
    }

    $sheet = $spreadsheet->getActiveSheet();

    $highestRow = $sheet->getHighestDataRow();

    $rows = $sheet->rangeToArray(
        'A1:C' . $highestRow,
        null,
        true,
        true,
        false
    );

    if (empty($rows)) {
        $this->Flash->error('ファイルが空です。');
        return $this->redirect(['action' => 'index']);
    }

    // HEADER CHECK
    $header = array_values(array_filter(array_map('trim', $rows[0])));
    $template = ['得意先CD', '名称', '電話番号'];

    if ($header !== $template) {
        $this->Flash->error('ヘッダーが正しくありません。テンプレートを使用してください。');
        return $this->redirect(['action' => 'index']);
    }

    array_shift($rows);

    $table = $this->MtPhonelists;

    $imported = 0;
    $skipped  = 0;
    $failed   = 0;

    $now    = date('Y-m-d H:i:s');
    $userId = $this->getCurrentUser()?->id ?? 0;

    foreach ($rows as $row) {

        // 🔥 STEP 1: trim + normalize
        $row = array_map(fn($v) => preg_replace('/\s+/u', '', trim($v ?? '')), $row);

        // 🔥 STEP 2: strict empty row skip
        if (count(array_filter($row, fn($v) => $v !== '')) === 0) {
            continue;
        }

        $row = array_slice($row, 0, 3);
        $row = array_pad($row, 3, '');

        [$customerCd, $name, $phone] = $row;

        // CLEAN PHONE
        $phone = mb_convert_kana($phone, 'asKV');
        $phone = preg_replace('/[^\d]/', '', $phone);

        // VALIDATION (STRICT)
        if (empty($customerCd) || empty($name) || empty($phone)) {
            $failed++;
            continue;
        }

        // DUPLICATE CHECK
        if ($table->exists(['phone' => $phone])) {
            $skipped++;
            continue;
        }

        $entity = $table->newEmptyEntity();

        $data = [
            'disp_cd'         => (int)$customerCd,
            'customer_cd'     => $customerCd,
            'item_name'       => $name,
            'phone'           => $phone,
            'active_flg'      => 1,
            'created'         => $now,
            'updated'         => $now,
            'created_user_id' => $userId,
            'updated_user_id' => $userId,
        ];

        $entity = $table->patchEntity($entity, $data);

        if ($table->save($entity)) {
            $imported++;
        } else {
            $failed++;
        }
    }

    $this->Flash->success(
        "取込結果：成功 {$imported}件 / 重複 {$skipped}件 / エラー {$failed}件"
    );

    return $this->redirect(['action' => 'index']);
}
}
