<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">ユーザ情報更新</h3>
    </div>
    <?= $this->Form->create($user, ['url' => ['controller' => 'Users', 'action' => 'edit', $user->id]]) ?>
    <?= $this->Form->control('id', ['type' => 'hidden']) ?>
    <table class="table">
        <tbody>
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">*ID</td>
                <td><?= $this->Form->control('disp_cd', [
                    'label' => false,
                    'div' => false,
                    'required' => true,
                    'type' => 'text',
                    'class' => 'form-control'
                ]) ?></td>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">*氏名</td>
                <td><?= $this->Form->control('user_name', [
                    'label' => false,
                    'div' => false,
                    'required' => true,
                    'type' => 'text',
                    'class' => 'form-control',
                    'style' => 'width:100%'
                ]) ?></td>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">*権限</td>
                <td><?= $this->Form->control('auth', [
                    'label' => false,
                    'div' => false,
                    'required' => true,
                    'type' => 'select',
                    'class' => 'form-control',
                    'style' => 'width:100%',
                    'options' => $authOpt ?? [],
                    'empty' => '-----'
                ]) ?></td>
            </tr>
            <tr>
            </tr>
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">*ログインID</td>
                <td><?= $this->Form->control('loginid', [
                    'label' => false,
                    'div' => false,
                    'required' => true,
                    'type' => 'text',
                    'class' => 'form-control',
                    'style' => 'width:100%',
                    'value' => ''
                ]) ?></td>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">*パスワード</td>
                <td><?= $this->Form->control('password', [
                    'label' => false,
                    'div' => false,
                    'required' => true,
                    'type' => 'password',
                    'class' => 'form-control',
                    'style' => 'width:100%',
                    'value' => ''
                ]) ?></td>
            </tr>
            <tr>
                <td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">登録者</td>
                <td width="18%"><?= isset($opeOpt[$user->created_user_id]) ? h($opeOpt[$user->created_user_id]) : '' ?></td>
                <td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">登録日時</td>
                <td width="18%"><?= $user->created ? h($user->created->format('Y-m-d H:i:s')) : '' ?></td>
                <td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">更新者</td>
                <td width="18%"><?= isset($opeOpt[$user->updated_user_id]) ? h($opeOpt[$user->updated_user_id]) : '' ?></td>
                <td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">更新日時</td>
                <td width="18%"><?= $user->updated ? h($user->updated->format('Y-m-d H:i:s')) : '' ?></td>
            </tr>
        </tbody>
    </table>
    <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
        <button type="button" style="width:150px;height:25px;cursor:pointer;" onclick="location.href='<?= $this->Url->build(['controller' => 'Users', 'action' => 'index']) ?>';" class="btn-default btn-xs">キャンセル</button>
        <?= $this->Form->postLink('削　除', ['action' => 'delete', $user->id], [
            'confirm' => __('削除します。よろしいですか？'),
            'class' => 'btn-danger btn-xs',
            'style' => 'width:150px;cursor:pointer',
            'escape' => false
        ]) ?>
        <?= $this->Form->button('更　新', [
            'div' => false,
            'name' => 'edit',
            'type' => 'submit',
            'style' => 'width:150px;cursor:pointer',
            'class' => 'btn-warning btn-xs'
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

