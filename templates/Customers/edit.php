<?php
$this->assign('title', '顧客詳細情報更新');
?>

<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">顧客詳細情報更新</h3>
    </div>

    <?= $this->Form->create($customer, ['url' => ['action' => 'edit', $customer->id]]) ?>

    <div class="panel-body">
        <!-- Row 1: 得意先CD & 会社・支店名 -->
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

        <!-- Row 2: 氏名 & 番号種別 -->
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

        <!-- Row 3: TEL & FAX -->
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

        <!-- Row 4: メール & メモ -->
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
                    'value' => $customer->memo ?? ''
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
                    <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">登録日</td>
                    <td><?= $customer->created ? h($customer->created->format('Y-m-d H:i:s')) : '' ?></td>
                    <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">登録者</td>
                    <td><?= isset($allUsersOpt[$customer->created_user_id]) ? h($allUsersOpt[$customer->created_user_id]) : '' ?></td>
                </tr>
                <tr>
                    <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">更新日</td>
                    <td><?= $customer->updated ? h($customer->updated->format('Y-m-d H:i:s')) : '' ?></td>
                    <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">更新者</td>
                    <td><?= isset($allUsersOpt[$customer->updated_user_id]) ? h($allUsersOpt[$customer->updated_user_id]) : '' ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Buttons -->
    <div class="panel-body text-center" style="margin-top:10px;">
        <?php
        $currentUser = $this->request->getAttribute('identity');
        ?>

        <!-- Delete button (outside main form to avoid nested forms) -->
        <?php if ($currentUser && $currentUser->getOriginalData()->auth != "0"): ?>
            <?= $this->Form->postLink('削　除', ['action' => 'delete', $customer->id], [
                'confirm' => __('顧客情報を削除します。よろしいですか？'),
                'class' => 'btn btn-danger btn-xs',
                'style' => 'width:150px;margin:5px;'
            ]) ?>
        <?php endif; ?>

        <!-- Cancel / Update buttons -->
        <?= $this->Form->button('キャンセル', [
            'type' => 'submit',
            'name' => 'back',
            'value' => '1',
            'class' => 'btn btn-default btn-xs',
            'style' => 'width:150px;margin:5px;'
        ]) ?>
        <?= $this->Form->button('更　新', [
            'type' => 'submit',
            'name' => 'add',
            'class' => 'btn btn-warning btn-xs',
            'style' => 'width:150px;margin:5px;'
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
