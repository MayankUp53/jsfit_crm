<?php
$this->assign('title', '対応履歴更新');
?>

<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴更新</h3>
    </div>

    <?= $this->Form->create($contact, ['url' => ['action' => 'edit', $contact->id]]) ?>
    <?= $this->Form->control('id', ['type' => 'hidden']) ?>
    <?= $this->Form->control('customer_id', ['type' => 'hidden', 'value' => $contact->customer_id]) ?>

    <div class="panel-body">
        <!-- 概要 -->
        <div class="form-group">
            <label>概要</label>
            <?= $this->Form->control('summary', [
                'label' => false,
                'type' => 'select',
                'options' => $overviewOpt ?? [],
                'empty' => '-----',
                'class' => 'form-control',
                'value' => $contact->summary
            ]) ?>
        </div>

        <!-- ステータス -->
        <div class="form-group">
            <label>ステータス</label>
            <?= $this->Form->control('status', [
                'label' => false,
                'type' => 'select',
                'options' => $statusOpt ??[],
                'empty' => '-----',
                'class' => 'form-control',
                'value' => $contact->status
            ]) ?>
        </div>

        <!-- 対応内容 -->
        <div class="form-group">
            <label>対応内容</label>
            <?= $this->Form->control('remark', [
                'label' => false,
                'type' => 'textarea',
                'rows' => 5,
                'class' => 'form-control',
                'value' => $contact->remark
            ]) ?>
        </div>
    </div>

    <div class="text-center" style="margin-top:15px;">
        <a href="<?= $this->Url->build(['controller' => 'Contacts', 'action' => 'add', $contact->customer_id]) ?>" class="btn btn-default btn-xs" style="width:150px;height:30px;margin-bottom:5px;">戻　る</a>
        <?= $this->Form->button('更　新', [
            'type' => 'submit',
            'name' => 'update',
            'class' => 'btn btn-info btn-xs',
            'style' => 'width:150px;height:30px;margin-bottom:5px;'
        ]) ?>
        <?= $this->Form->postLink('削　除', ['action' => 'delete', $contact->id], [
            'confirm' => '削除しますか？',
            'class' => 'btn btn-danger btn-xs',
            'style' => 'width:150px;height:30px;margin-bottom:5px;'
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
