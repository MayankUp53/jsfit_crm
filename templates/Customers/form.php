<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Customer $customer
 */
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">顧客詳細情報 hhheheheheehheehhhhheeeeeeee<?= $customer->isNew() ? '新規登録' : '更新' ?></h3>
    </div>

    <?= $this->Form->create($customer, ['url' => $customer->isNew() ? ['action' => 'add'] : ['action' => 'edit', $customer->id]]) ?>
    <?php if (!$customer->isNew()): ?>
        <?= $this->Form->control('id', ['type' => 'hidden']) ?>
    <?php endif; ?>

    <div class="panel panel-default section-panel">
        <div class="panel-heading"><strong>基本情報</strong></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('disp_no', ['label' => '表示No', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('name', ['label' => '氏名', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('name_kj', ['label' => '氏名(漢字)', 'class' => 'form-control']) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('name_kn', ['label' => '氏名(カナ)', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('sex', ['label' => '性別', 'type' => 'select', 'options' => $sexOpt ?? [], 'empty' => '-----', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('birth', ['label' => '生年月日', 'class' => 'form-control datepicker', 'type' => 'text']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default section-panel">
        <div class="panel-heading"><strong>連絡先</strong></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('phone', ['label' => '電話番号', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('tel', ['label' => '携帯番号', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('email', ['label' => 'メール', 'class' => 'form-control']) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('fax', ['label' => 'FAX', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('zip_cd', ['label' => '郵便番号', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('addr1', ['label' => '住所1', 'class' => 'form-control']) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <?= $this->Form->control('addr2', ['label' => '住所2', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-6 form-group">
                    <?= $this->Form->control('addr3', ['label' => '住所3', 'class' => 'form-control']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default section-panel">
        <div class="panel-heading"><strong>備考</strong></div>
        <div class="panel-body">
            <?= $this->Form->control('remark', ['label' => '備考', 'type' => 'textarea', 'rows' => 3, 'class' => 'form-control']) ?>
            <div class="row" style="margin-top:10px;">
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('ex1', ['label' => '拡張1', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('ex2', ['label' => '拡張2', 'class' => 'form-control']) ?>
                </div>
                <div class="col-sm-4 form-group">
                    <?= $this->Form->control('ex3', ['label' => '拡張3', 'class' => 'form-control']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="btn_margin" align="center" style="width:95%;margin-bottom:5px;">
        <?php if ($customer->isNew()): ?>
            <?= $this->Form->button('キャンセル', ['name' => 'back', 'type' => 'submit', 'class' => 'btn-default btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?php else: ?>
            <?= $this->Form->button('顧客検索へ戻る', ['name' => 'back', 'type' => 'submit', 'class' => 'btn-default btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
            <?= $this->Form->button('削　除', ['name' => 'delete', 'type' => 'submit', 'class' => 'btn-danger btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer', 'formaction' => $this->Url->build(['action' => 'delete', $customer->id]), 'formmethod' => 'post']) ?>
        <?php endif; ?>
        <?= $this->Form->button($customer->isNew() ? '登　録' : '更　新', ['type' => 'submit', 'class' => 'btn-info btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<script>
    $(function() {
        $(".datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:+0",
            dateFormat: 'yy-mm-dd'
        });
    });
</script>
