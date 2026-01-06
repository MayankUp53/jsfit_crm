<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">ユーザ一覧</h3>
        <span style="float:right;margin-top:-20px">
            <?= $this->Paginator->counter('{{start}} - {{end}} / {{count}} 件表示') ?>
        </span>
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

    <!-- Bootstrap-style Pagination -->
    <div class="paginateLinks btn_margin" style="width:100%" align="center">
        <ul class="pagination pagination-sm justify-content-center">
            <?= $this->Paginator->first('<< ' . __('最初'), ['class' => 'page-item']) ?>
            <?= $this->Paginator->prev('前へ ' . __('前'), ['class' => 'page-item']) ?>
            <?= $this->Paginator->numbers([
                'before' => '',
                'after' => '',
                'class' => 'page-item',
                'currentClass' => 'active',
                'modulus' => 5
            ]) ?>
            <?= $this->Paginator->next(__('次') . ' 次へ', ['class' => 'page-item']) ?>
            <?= $this->Paginator->last(__('最後') . ' >>', ['class' => 'page-item']) ?>
        </ul>
    </div>

    <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
        <button style="width:150px;height:30px;cursor:pointer;" onclick="location.href='<?= $this->Url->build(['action' => 'add']) ?>';" class="btn-info btn-xs">新規作成</button>
    </div>
</div>
