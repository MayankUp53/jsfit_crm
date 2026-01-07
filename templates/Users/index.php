<?php
$this->assign('title', 'ユーザ一覧');
?>

<!-- ================= Users Table ================= -->
<div class="container-fluid" style="margin-top:20px; padding-left:5px; padding-right:5px;">
    <table id="usersTable" class="table table-striped table-bordered table-hover" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>氏名</th>
                <th>権限</th>
                <th>ログインID</th>
                <th>パスワード</th>
                <th>登録日</th>
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
                <td><?= h($user->created?->format('Y-m-d')) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


    <div style="text-align:center;margin-top:15px;">
        <button class="btn btn-info btn-xs" style="width:150px;height:30px;"
                onclick="location.href='<?= $this->Url->build(['action' => 'add']) ?>'">
            新規作成
        </button>
    </div>
</div>


<?php $this->start('script'); ?>
<script>
jQuery(document).ready(function($) {
    var table = $('#usersTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        searching: true,
        responsive: true,
        language: {
            search: "ユーザを検索:",
            paginate: { previous: "前", next: "次" },
            info: "_START_ - _END_ / _TOTAL_ 件表示",
            infoEmpty: "0 件表示",
            zeroRecords: "該当データがありません"
        },
        // Move search box to the left
        dom: '<"row"<"col-sm-6"f><"col-sm-6"l>>tip' // f=filter, l=length
    });

 
});
</script>
<?php $this->end(); ?>
