<?php
/** @var \App\View\AppView $this */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴詳細</h3>
    </div>
    <table class="table table-bordered">
        <tr>
            <th width="20%">履歴ID</th>
            <td><?= h($contact->id) ?></td>
        </tr>
        <tr>
            <th>顧客</th>
            <td>
                <?= h($customer->name) ?> (ID: <?= h($customer->id) ?>)
                <div style="margin-top:5px;">
                    電話: <?= h($customer->phone) ?> / メール: <?= h($customer->email) ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>ステータス</th>
            <td><?= h($statusOpt[$contact->status] ?? $contact->status) ?></td>
        </tr>
        <tr>
            <th>キュー</th>
            <td><?= h($contact->queue_name) ?></td>
        </tr>
        <tr>
            <th>架電番号</th>
            <td><?= h($contact->phone) ?></td>
        </tr>
        <tr>
            <th>再コール</th>
            <td>
                <?= $contact->callback_datetime ? h($contact->callback_datetime->format('Y-m-d H:i')) : '' ?>
                （<?= h($callbackStatusOpt[$contact->is_done] ?? $contact->is_done) ?>）
            </td>
        </tr>
        <tr>
            <th>概要</th>
            <td><?= nl2br(h($contact->summary)) ?></td>
        </tr>
        <tr>
            <th>対応内容</th>
            <td><?= nl2br(h($contact->detail)) ?></td>
        </tr>
        <tr>
            <th>メモ</th>
            <td><?= nl2br(h($contact->remark)) ?></td>
        </tr>
        <tr>
            <th>登録日時</th>
            <td><?= $contact->created ? h($contact->created->format('Y-m-d H:i:s')) : '' ?></td>
        </tr>
        <tr>
            <th>更新日時</th>
            <td><?= $contact->updated ? h($contact->updated->format('Y-m-d H:i:s')) : '' ?></td>
        </tr>
    </table>

    <div class="btn_margin" style="margin-bottom:15px;">
        <a class="btn btn-xs btn-primary" href="<?= $this->Url->build(['action' => 'edit', $contact->id]) ?>">編集</a>
        <a class="btn btn-xs btn-default" href="<?= $this->Url->build(['action' => 'index']) ?>">一覧へ</a>
        <a class="btn btn-xs btn-info" href="<?= $this->Url->build(['action' => 'add', $contact->customer_id]) ?>">新規登録</a>
    </div>
</div>

<?php if (!empty($contacts)): ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">顧客の対応履歴</h3>
        </div>
        <table class="table table-striped table-hover" style="font-size:12px">
            <thead>
                <tr>
                    <th width="8%">履歴ID</th>
                    <th width="14%">ステータス</th>
                    <th width="14%">架電番号</th>
                    <th width="36%">概要</th>
                    <th width="14%">登録日</th>
                    <th width="14%">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $row): ?>
                    <tr>
                        <td><?= h($row->id) ?></td>
                        <td><?= h($statusOpt[$row->status] ?? $row->status) ?></td>
                        <td><?= h($row->phone) ?></td>
                        <td><?= h(mb_strimwidth((string)$row->summary, 0, 60, '...')) ?></td>
                        <td><?= $row->created ? h($row->created->format('Y-m-d H:i')) : '' ?></td>
                        <td>
                            <a href="<?= $this->Url->build(['action' => 'view', $row->id]) ?>" class="btn btn-xs btn-default">詳細</a>
                            <a href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>" class="btn btn-xs btn-primary" style="margin-left:3px;">編集</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
