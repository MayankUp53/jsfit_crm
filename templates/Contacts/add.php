<?php
$this->assign('title', '対応履歴登録');
$currentUser = $this->request->getAttribute('identity');
$userAuth = $currentUser ? ($currentUser->getOriginalData()->auth ?? null) : null;
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴登録</h3>
    </div>

    <table class="table table-bordered">
        <tr>
            <th width="15%">顧客ID</th>
            <td width="35%"><?= h($customer->id) ?></td>
            <th width="15%">氏名</th>
            <td width="35%"><?= h($customer->name) ?></td>
        </tr>
        <tr>
            <th>電話番号</th>
            <td><?= h($customer->phone) ?></td>
            <th>メール</th>
            <td><?= h($customer->email) ?></td>
        </tr>
        <tr>
            <th>FAX</th>
            <td><?= h($customer->fax) ?></td>
            <th>作成日時</th>
            <td><?= $customer->created ? h($customer->created->format('Y-m-d H:i')) : '' ?></td>
        </tr>
    </table>

    <?= $this->Form->create($contact, ['url' => ['action' => 'add', $customer->id]]) ?>
    <?= $this->Form->control('customer_id', ['type' => 'hidden', 'value' => $customer->id]) ?>

    <div class="row">
        <div class="col-sm-4 form-group">
            <?= $this->Form->control('status', ['label' => 'ステータス', 'type' => 'select', 'options' => $statusOpt ?? [], 'empty' => '-----', 'class' => 'form-control']) ?>
        </div>
        <div class="col-sm-4 form-group">
            <?= $this->Form->control('queue_name', ['label' => 'キュー', 'type' => 'select', 'options' => $queueOpt ?? [], 'empty' => '-----', 'class' => 'form-control']) ?>
        </div>
        <div class="col-sm-4 form-group">
            <?= $this->Form->control('phone', ['label' => '架電番号', 'class' => 'form-control']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 form-group">
            <?= $this->Form->control('callback_datetime', ['label' => '再コール日時', 'class' => 'form-control datetimepicker', 'type' => 'text']) ?>
        </div>
        <div class="col-sm-4 form-group">
            <?= $this->Form->control('is_done', ['label' => '再コール状態', 'type' => 'select', 'options' => $callbackStatusOpt ?? [], 'empty' => '-----', 'class' => 'form-control']) ?>
        </div>
    </div>

    <?= $this->Form->control('summary', ['label' => '概要', 'type' => 'textarea', 'rows' => 2, 'class' => 'form-control']) ?>
    <?= $this->Form->control('detail', ['label' => '対応内容', 'type' => 'textarea', 'rows' => 4, 'class' => 'form-control']) ?>
    <?= $this->Form->control('remark', ['label' => 'メモ', 'type' => 'textarea', 'rows' => 3, 'class' => 'form-control']) ?>

    <div class="btn_margin" align="center" style="width:95%;margin-top:10px;">
        <a href="<?= $this->Url->build(['controller' => 'Customers', 'action' => 'index']) ?>" class="btn btn-default btn-xs" style="width:150px;height:30px;cursor:pointer">戻　る</a>
        <?= $this->Form->button('登　録', ['name' => 'add', 'type' => 'submit', 'class' => 'btn-info btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴一覧</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover" style="font-size:12px">
            <thead>
                <tr>
                    <th width="8%">履歴ID</th>
                    <th width="15%">ステータス</th>
                    <th width="15%">架電番号</th>
                    <th width="30%">概要</th>
                    <th width="15%">登録日</th>
                    <th width="17%">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $row): ?>
                    <tr>
                        <td><?= h($row->id) ?></td>
                        <td><?= h($statusOpt[$row->status] ?? $row->status) ?></td>
                        <td><?= h($row->phone) ?></td>
                        <td><?= h(mb_strimwidth((string)$row->summary, 0, 50, '...')) ?></td>
                        <td><?= $row->created ? h($row->created->format('Y-m-d H:i')) : '' ?></td>
                        <td>
                            <a href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>" class="btn btn-xs btn-primary">編集</a>
                            <?php if ($userAuth !== null && $userAuth != 0): ?>
                                <?= $this->Form->postLink('削除', ['action' => 'delete', $row->id], ['confirm' => '削除しますか？', 'class' => 'btn btn-xs btn-danger', 'style' => 'margin-left:5px;']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(function() {
        $(".datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd'
        });
        if ($.fn.datetimepicker) {
            $(".datetimepicker").datetimepicker({
                format: 'Y-m-d H:i'
            });
        }
    });
</script>
