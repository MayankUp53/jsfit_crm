<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Customer $customer
 * @var array $contacts
 */
$base = [
    ['label' => '顧客ID', 'value' => $customer->id],
    ['label' => '表示No', 'value' => $customer->disp_no],
    ['label' => '氏名', 'value' => $customer->name],
    ['label' => '氏名(漢字)', 'value' => $customer->name_kj],
    ['label' => '氏名(カナ)', 'value' => $customer->name_kn],
    ['label' => '電話番号', 'value' => $customer->phone],
    ['label' => '携帯番号', 'value' => $customer->tel],
    ['label' => 'メール', 'value' => $customer->email],
    ['label' => 'FAX', 'value' => $customer->fax],
    ['label' => '郵便番号', 'value' => $customer->zip_cd],
    ['label' => '住所1', 'value' => $customer->addr1],
    ['label' => '住所2', 'value' => $customer->addr2],
    ['label' => '住所3', 'value' => $customer->addr3],
    ['label' => '性別', 'value' => $sexOpt[$customer->sex] ?? ''],
    ['label' => '生年月日', 'value' => $customer->birth ? $customer->birth->format('Y-m-d') : ''],
    ['label' => '備考', 'value' => $customer->remark],
    ['label' => '登録日', 'value' => $customer->created ? $customer->created->format('Y-m-d H:i:s') : ''],
    ['label' => '更新日', 'value' => $customer->updated ? $customer->updated->format('Y-m-d H:i:s') : ''],
];
?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">顧客詳細</h3>
    </div>
    <table class="table">
        <tbody>
            <?php foreach (array_chunk($base, 3) as $row): ?>
                <tr>
                    <?php foreach ($row as $item): ?>
                        <td width="12%" class="detail-label"><?= h($item['label']) ?></td>
                        <td width="22%"><?= nl2br(h($item['value'])) ?></td>
                    <?php endforeach; ?>
                    <?php for ($i = count($row); $i < 3; $i++): ?>
                        <td width="12%" class="detail-label"></td>
                        <td width="22%"></td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="btn_margin" style="margin-bottom:20px;">
    <?= $this->Html->link('編集', ['action' => 'edit', $customer->id], ['class' => 'btn btn-warning btn-xs']) ?>
    <?= $this->Html->link('顧客一覧', ['action' => 'index'], ['class' => 'btn btn-default btn-xs']) ?>
    <?= $this->Html->link('対応履歴登録', ['controller' => 'Contacts', 'action' => 'add', $customer->id], ['class' => 'btn btn-info btn-xs']) ?>
</div>

<?php if (!empty($contacts)): ?>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">対応履歴</h3>
        </div>
        <table class="table table-striped table-hover" style="font-size:12px;">
            <thead>
                <tr>
                    <th>履歴ID</th>
                    <th>ステータス</th>
                    <th>電話番号</th>
                    <th>概要</th>
                    <th>登録日</th>
                    <th>更新日</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $row): ?>
                    <tr>
                        <td><?= h($row->id) ?></td>
                        <td><?= isset($statusOpt[$row->status]) ? h($statusOpt[$row->status]) : '' ?></td>
                        <td><?= h($row->phone) ?></td>
                        <td><?= h($row->summary) ?></td>
                        <td><?= $row->created ? h($row->created->format('Y-m-d H:i')) : '' ?></td>
                        <td><?= $row->updated ? h($row->updated->format('Y-m-d H:i')) : '' ?></td>
                        <td>
                            <?= $this->Html->link('詳細', ['controller' => 'Contacts', 'action' => 'view', $row->id], ['class' => 'btn btn-xs btn-default']) ?>
                            <?= $this->Html->link('編集', ['controller' => 'Contacts', 'action' => 'edit', $row->id], ['class' => 'btn btn-xs btn-primary', 'style' => 'margin-left:3px;']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
    .detail-label {
        vertical-align: middle;
        color: #ffffff;
        background-color: #4169E1;
    }
</style>
