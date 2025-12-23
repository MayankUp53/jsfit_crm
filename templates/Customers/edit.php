<?php
$this->assign('title', '顧客詳細情報更新');
?>

<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">顧客詳細情報更新</h3>
    </div>

    <?= $this->Form->create($customer, ['url' => ['action' => 'edit', $customer->id]]) ?>

    <div class="panel-body">
        <div class="row">
            <!-- 得意先CD -->
            <div class="col-sm-6 form-group">
                <label>得意先CD</label>
                <?= $this->Form->control('client_cd', ['label' => false, 'class' => 'form-control', 'type' => 'number']) ?>
            </div>

            <!-- 会社・支店名 -->
            <div class="col-sm-6 form-group">
                <label>会社・支店名</label>
                <?= $this->Form->control('company_name', ['label' => false, 'class' => 'form-control']) ?>
            </div>
        </div>

        <div class="row">
            <!-- 氏名 -->
            <div class="col-sm-6 form-group">
                <label>氏名</label>
                <?= $this->Form->control('name', ['label' => false, 'class' => 'form-control']) ?>
            </div>

            <!-- 番号種別 -->
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

        <div class="row">
            <!-- TEL -->
            <div class="col-sm-6 form-group">
                <label>TEL</label>
                <?= $this->Form->control('tel', ['label' => false, 'class' => 'form-control']) ?>
            </div>

            <!-- FAX -->
            <div class="col-sm-6 form-group">
                <label>FAX</label>
                <?= $this->Form->control('fax', ['label' => false, 'class' => 'form-control']) ?>
            </div>
        </div>

        <div class="row">
            <!-- メールアドレス -->
            <div class="col-sm-12 form-group">
                <label>メールアドレス</label>
                <?= $this->Form->control('email', ['label' => false, 'class' => 'form-control', 'type' => 'email']) ?>
            </div>
        </div>
    </div>

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

    <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
        <?php
        $currentUser = $this->request->getAttribute('identity');
        if ($currentUser && $currentUser->getOriginalData()->auth != "0") {
            echo $this->Form->postLink('削　除', ['action' => 'delete', $customer->id], [
                'confirm' => __('顧客情報を削除します。よろしいですか？'),
                'class' => 'btn btn-danger btn-xs',
                'style' => 'width:150px;cursor:pointer',
                'escape' => false
            ]);
        }
        ?>
        <?= $this->Form->button('キャンセル', [
            'div' => false,
            'value' => '1',
            'name' => 'back',
            'type' => 'submit',
            'style' => 'width:150px;cursor:pointer',
            'class' => 'btn btn-default btn-xs'
        ]) ?>
        <?= $this->Form->button('更　新', [
            'div' => false,
            'name' => 'add',
            'type' => 'submit',
            'style' => 'width:150px;cursor:pointer',
            'class' => 'btn btn-warning btn-xs'
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
