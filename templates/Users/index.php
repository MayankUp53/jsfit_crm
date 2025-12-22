<?php
/**
 * ユーザ一覧 (legacy parity).
 */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">ユーザ一覧</h3>
        <span style="float:right;margin-top:-20px"><?= count($users) ?>件</span>
    </div>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">ID</th>
                <th width="15%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">氏名</th>
                <th width="15%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">権限</th>
                <th width="15%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">ログインＩＤ</th>
                <th width="15%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">パスワード</th>
                <th width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">登録日</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr ondblclick="location.href='<?= $this->Url->build(['action' => 'edit', $user->id]) ?>'">
                    <td><?= h($user->disp_cd) ?></td>
                    <td><?= h($user->user_name) ?></td>
                    <td><?= h($authOpt[$user->auth] ?? $user->auth) ?></td>
                    <td><?= h($user->loginid) ?></td>
                    <td><?= h($user->password) ?></td>
                    <td><?= h($user->created) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
        <button style="width:150px;height:30px;cursor:pointer;" onclick="location.href='<?= $this->Url->build(['action' => 'add']) ?>';" class="btn-info btn-xs">新規作成</button>
    </div>
</div>
