<?php
$this->assign('title', '対応履歴更新');
$currentUser = $this->request->getAttribute('identity');
$userAuth = $currentUser ? ($currentUser->getOriginalData()->auth ?? null) : null;
?>
<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴更新</h3>
    </div>
    <?= $this->Form->create($contact, ['url' => ['action' => 'edit', $contact->id]]) ?>
    <?= $this->Form->control('id', ['type' => 'hidden']) ?>
    <?= $this->Form->control('customer_id', ['type' => 'hidden', 'value' => $contact->customer_id]) ?>

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
        <a href="<?= $this->Url->build(['action' => 'view', $contact->id]) ?>" class="btn btn-default btn-xs" style="width:150px;height:30px;cursor:pointer">詳細へ戻る</a>
        <?= $this->Form->button('更　新', ['type' => 'submit', 'class' => 'btn-info btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?php if ($userAuth !== null && $userAuth != 0): ?>
            <?= $this->Form->postLink('削　除', ['action' => 'delete', $contact->id], ['confirm' => '削除しますか？', 'class' => 'btn-danger btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?php endif; ?>
    </div>
    <?= $this->Form->end() ?>
</div>
