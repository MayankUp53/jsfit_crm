<?php
$this->assign('title', '電話帳');
?>

<div class="container-fluid" style=" padding-left:5px; padding-right:5px;">

    <!-- ================= TITLE ================= -->
    <h4 style="margin-bottom:10px; font-weight:bold;">
        顧客電話番号一括取込
    </h4>

    <!-- ================= IMPORT SECTION ================= -->
    <div style="padding:10px; border:1px solid #ddd; margin-bottom:15px;">

        <?= $this->Form->create(null, [
            'url' => ['controller' => 'MtPhonelists', 'action' => 'import'],
            'type' => 'file'
        ]) ?>

        <div style="display:flex; gap:10px; align-items:center;">

            <div style="flex:1;">
                <?= $this->Form->control('result', [
                    'type' => 'file',
                    'label' => false,
                    'required' => true
                ]) ?>
            </div>

            <button type="button"
                class="btn btn-warning btn-xs"
                onclick="location.href='<?= $this->Url->build(['action' => 'template']) ?>'">
                雛形DL
            </button>

            <?= $this->Form->button('取込', [
                'class' => 'btn btn-info btn-xs',
                'onclick' => 'return confirm("Importしますか？");'
            ]) ?>

        </div>

        <?= $this->Form->end() ?>

    </div>

    <!-- ================= TABLE ================= -->
    <table id="phoneTable" class="table table-striped table-bordered table-hover" style="width:100%">

        <thead>
            <tr>
                <th>表示順</th>
                <th>名称</th>
                <th>電話番号</th>
                <th>有効無効</th>
                <th>操作</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($mtPhonelists as $row): ?>
            <tr ondblclick="location.href='<?= $this->Url->build(['action' => 'edit', $row->id]) ?>'">

                <td><?= h($row->disp_cd) ?></td>
                <td><?= h($row->item_name) ?></td>
                <td><?= h($row->phone) ?></td>
                <td><?= h($activeOpt[$row->active_flg] ?? '') ?></td>

                <td>
                    <a class="btn btn-xs btn-success"
                       href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>">
                        編集
                    </a>

                    <?= $this->Form->postLink(
                        '削除',
                        ['action' => 'delete', $row->id],
                        [
                            'confirm' => '削除しますか？',
                            'class' => 'btn btn-xs btn-danger'
                        ]
                    ) ?>
                </td>

            </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

    <!-- ================= FOOTER ================= -->
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

    $('#phoneTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        searching: true,
        responsive: true,

        dom: '<"row"<"col-sm-6"f><"col-sm-6"l>>tip',

        language: {
            search: "電話帳検索:",
            paginate: { previous: "前", next: "次" },
            info: "_START_ - _END_ / _TOTAL_ 件表示",
            infoEmpty: "0 件表示",
            zeroRecords: "該当データがありません"
        }
    });

});
</script>
<?php $this->end(); ?>