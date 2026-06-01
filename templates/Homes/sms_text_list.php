<?php
/**
 * SMS text single-select pop-up.
 */
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css">
</head>

<body>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">SMS文面一覧</h3>
    </div>

    <table id="dt" class="table table-striped table-hover">
        <thead>
        <tr height="35" style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">
            <th width="10%">ID</th>
            <th width="25%">名称</th>
            <th width="50%">SMS文面</th>
            <th class="actions">操作</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($mtSmsTexts as $row): ?>
            <tr>
                <td><?= h($row->id) ?></td>
                <td><?= h($row->item_name) ?></td>
                <td><?= h($row->sms_text) ?></td>
                <td class="actions"></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>

<script>
$(function () {
    var urlParams = new URLSearchParams(window.location.search);
    var targetField = urlParams.get('target') || 'sms_text';

    var table = $('#dt').DataTable({
        dom: '<"pull-left"f><"pull-right"l>ti<"pull-left"p>',
        order: [[0, "asc"]],
        language: {
            url: "//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Japanese.json"
        },
        columnDefs: [{
            targets: -1,
            data: null,
            orderable: false,
            searchable: false,
            defaultContent: '<button type="button" class="btn btn-xs btn-info sel_btn">選択</button>'
        }]
    });

    $('#dt tbody').on('click', 'button.sel_btn', function () {
        var data = table.row($(this).parents('tr')).data();
        var smsText = (data && data[2]) ? String(data[2]).trim() : '';

        if (window.opener && !window.opener.closed) {
            var input = window.opener.document.getElementById(targetField);

            if (input) {
                input.value = smsText;
                input.dispatchEvent(new Event('input'));
                input.dispatchEvent(new Event('change'));
            }
        }

        window.close();
    });
});
</script>

</body>
</html>
