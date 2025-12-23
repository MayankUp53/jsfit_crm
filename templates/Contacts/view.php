<?php
$this->assign('title', '対応履歴詳細');

// Main contact information to display
$contactInfo = [
    ['label' => '得意先CD', 'value' => $contact->customer->client_cd ?? ''],
    ['label' => '会社・支店名', 'value' => $contact->customer->company_name ?? ''],
    ['label' => '顧客氏名', 'value' => $contact->customer->name ?? ''],
    ['label' => '顧客電話', 'value' => $contact->customer->tel ?? ''],
    ['label' => '顧客メール', 'value' => $contact->customer->email ?? ''],
    ['label' => '概要', 'value' => $contact->summary ?? ''],
    ['label' => 'ステータス', 'value' => $contact->status ?? ''],
    ['label' => '対応内容', 'value' => $contact->remark ?? ''],
    ['label' => '担当OP', 'value' => $contact->assigned_operator ?? ''],
    ['label' => '登録日時', 'value' => $contact->created ? $contact->created->format('Y-m-d H:i:s') : ''],
    ['label' => '更新日時', 'value' => $contact->updated ? $contact->updated->format('Y-m-d H:i:s') : ''],
];
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴詳細</h3>
    </div>

    <table class="table table-bordered">
        <?php foreach ($contactInfo as $item): ?>
            <tr>
                <th width="20%"><?= h($item['label']) ?></th>
                <td><?= nl2br(h($item['value'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="btn_margin" style="margin-top:10px;">
        <a class="btn btn-xs btn-primary" href="<?= $this->Url->build(['action' => 'edit', $contact->id]) ?>">編集</a>
        <a class="btn btn-xs btn-default" href="<?= $this->Url->build(['action' => 'add', $contact->customer_id]) ?>">一覧へ</a>
    </div>
</div>
