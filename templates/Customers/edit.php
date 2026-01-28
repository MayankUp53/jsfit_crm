<?php
$this->assign('title', '顧客詳細情報更新');
?>

<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">顧客詳細情報更新</h3>
    </div>

    <!-- ========================= -->
    <!-- EDIT FORM START -->
    <!-- ========================= -->
    <?= $this->Form->create($customer, ['url' => ['action' => 'edit', $customer->id]]) ?>

    <div class="panel-body">

        <!-- Row 1 -->
        <div class="row">
            <div class="col-sm-6 form-group">
                <label>得意先CD</label>
                <?= $this->Form->control('client_cd', ['label' => false, 'class' => 'form-control']) ?>
            </div>
            <div class="col-sm-6 form-group">
                <label>会社・支店名</label>
                <?= $this->Form->control('company_name', ['label' => false, 'class' => 'form-control']) ?>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="row">
            <div class="col-sm-6 form-group">
                <label>氏名</label>
                <?= $this->Form->control('name', ['label' => false, 'class' => 'form-control']) ?>
            </div>
            <div class="col-sm-6 form-group">
                <label>番号種別</label>
                <?= $this->Form->control('type_of_number', [
                    'label' => false,
                    'type' => 'select',
                    'options' => [
                        '営業所' => '営業所',
                        '私用携帯' => '私用携帯',
                        '固定電話' => '固定電話'
                    ],
                    'empty' => '-----',
                    'class' => 'form-control'
                ]) ?>
            </div>
        </div>

        <!-- Row 3 -->
        <div class="row">
            <div class="col-sm-6 form-group">
                <label>TEL</label>
                <?= $this->Form->control('tel', ['label' => false, 'class' => 'form-control']) ?>
            </div>
            <div class="col-sm-6 form-group">
                <label>FAX</label>
                <?= $this->Form->control('fax', ['label' => false, 'class' => 'form-control']) ?>
            </div>
        </div>

        <!-- Row 4 -->
        <div class="row">
            <div class="col-sm-6 form-group">
                <label>メールアドレス</label>
                <?= $this->Form->control('email', [
                    'label' => false,
                    'class' => 'form-control',
                    'type' => 'email'
                ]) ?>
            </div>
            <div class="col-sm-6 form-group">
                <label>メモ</label>
                <?= $this->Form->control('memo', [
                    'label' => false,
                    'class' => 'form-control',
                    'type' => 'textarea',
                    'rows' => 4,
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Registration Info -->
    <div class="panel panel-default">
        <div class="panel-heading"><strong>登録情報</strong></div>
        <table class="table">
            <tbody>
                <tr>
                    <td bgcolor="#ff8c00" style="color:#fff;">登録日</td>
                    <td><?= $customer->created ? h($customer->created->format('Y-m-d H:i:s')) : '' ?></td>
                    <td bgcolor="#ff8c00" style="color:#fff;">登録者</td>
                    <td><?= $allUsersOpt[$customer->created_user_id] ?? '' ?></td>
                </tr>
                <tr>
                    <td bgcolor="#ff8c00" style="color:#fff;">更新日</td>
                    <td><?= $customer->updated ? h($customer->updated->format('Y-m-d H:i:s')) : '' ?></td>
                    <td bgcolor="#ff8c00" style="color:#fff;">更新者</td>
                    <td><?= $allUsersOpt[$customer->updated_user_id] ?? '' ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ========================= -->
    <!-- BUTTON ROW (All in one line) -->
    <!-- ========================= -->
    <div class="panel-body text-center" style="margin-top:10px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap; align-items:center;">

        <?php $currentUser = $this->request->getAttribute('identity'); ?>

        <!-- DELETE BUTTON -->
        <?php if ($currentUser && $currentUser->getOriginalData()->auth != "0"): ?>
            <?= $this->Form->button('削　除', [
                'type' => 'submit',
                'name' => 'delete',
                'value' => '1',
                'class' => 'btn btn-danger btn-xs',
                'style' => 'width:150px;',
                'confirm' => '本当に削除してもよろしいですか？'
            ]) ?>
        <?php endif; ?>

        <!-- CANCEL BUTTON -->
        <?= $this->Form->button('キャンセル', [
            'type' => 'submit',
            'name' => 'back',
            'value' => '1',
            'class' => 'btn btn-default btn-xs',
            'style' => 'width:150px;'
        ]) ?>

        <!-- UPDATE BUTTON -->
        <?= $this->Form->button('更　新', [
            'type' => 'submit',
            'name' => 'add',
            'class' => 'btn btn-warning btn-xs',
            'style' => 'width:150px;'
        ]) ?>

    </div>

    <!-- ========================= -->
    <!-- CLOSE EDIT FORM -->
    <!-- ========================= -->
    <?= $this->Form->end() ?>

</div>
