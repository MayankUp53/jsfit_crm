<?php
/**
 * @var \App\View\AppView $this
 */
?>
<br />
<br />
<br />
<br />
<br />
<div style="width:100%;" align="center" class="login">
    <img src="<?= $this->Url->image('logo.gif') ?>" alt="Logo" />
    <br />
    <br />
    <div class="panel panel-info" style="width:50%;">
        <div class="panel-heading">
            <h3 class="panel-title">ログイン</h3>
        </div>
        <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'login']]) ?>
        <table class="table">
            <tbody>
                <tr>
                    <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">ユーザＩＤ</td>
                    <td width="25%">
                        <?= $this->Form->control('loginid', [
                            'label' => false,
                            'div' => false,
                            'type' => 'text',
                            'class' => 'form-control',
                            'required' => true
                        ]) ?>
                    </td>
                    <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">パスワード</td>
                    <td width="25%">
                        <?= $this->Form->control('password', [
                            'label' => false,
                            'div' => false,
                            'type' => 'password',
                            'class' => 'form-control',
                            'required' => true
                        ]) ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
            <?= $this->Form->button('ログイン', [
                'div' => false,
                'name' => 'login',
                'type' => 'submit',
                'style' => 'width:150px;height:30px;cursor:pointer'
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
<br />
<br />
<br />
<br />
<br />

