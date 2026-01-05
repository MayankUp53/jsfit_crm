<?php $this->start('script'); ?>
<script>
$(function() {
    $("#jquery-ui-sortable").sortable();
    $("#jquery-ui-sortable").disableSelection();

    $("#submitSortable").on("click", function() {
        var itemIDs = "";
        $("#jquery-ui-sortable li").each(function() {
            itemIDs += $(this).find("span.sort-id").text().trim() + "-";
        });

        if (confirm("この順番で更新します。よろしいですか？")) {
            // /mt-phonelists/sortupdate/{ids}
            location.href = "<?= $this->Url->build(['controller' => 'MtPhonelists', 'action' => 'sortupdate', '']) ?>" + itemIDs;
        }
    });
});
</script>
<?php $this->end(); ?>

<?php $this->start('css'); ?>
<style>
#jquery-ui-sortable {
    list-style-type: none;
    margin: 0;
    padding: 0;
    width: 50%;
}
#jquery-ui-sortable li {
    margin: 0 3px 3px 3px;
    padding: 0.6em 0.8em;
    font-size: 15px;
    font-weight: bold;
    cursor: move;
    border-radius: 4px;
}
#jquery-ui-sortable li.inactive {
    background-color: #dcdcdc;
}
#jquery-ui-sortable .actions {
    float: right;
    margin-top: -2px;
}
#jquery-ui-sortable .meta {
    font-weight: normal;
    margin-left: 10px;
}
</style>
<?php $this->end(); ?>

<div class="panel panel-primary filterable">
    <div class="panel-heading">
        <h3 class="panel-title">電話帳</h3>
    </div>

    <br />

    <ul id="jquery-ui-sortable">
        <?php foreach ($mtPhonelists as $row): ?>
            <?php $inactive = ((string)$row->active_flg !== '1'); ?>
            <li class="ui-state-default <?= $inactive ? 'inactive' : '' ?>">
                <span class="sort-id" style="display:none;"><?= h($row->id) ?></span>

                <?= h($row->disp_cd) ?>.
                <?= h($row->item_name) ?>
                <span class="meta">
                    <?= h($row->phone) ?>
                    (<?= h($activeOpt[$row->active_flg] ?? '') ?>)
                </span>

                <span class="actions">
                    <a class="btn btn-xs btn-success"
                       href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>">編集</a>

                    <?= $this->Form->postLink(
                        '削除',
                        ['action' => 'delete', $row->id],
                        ['confirm' => '削除しますか？', 'class' => 'btn btn-xs btn-danger']
                    ) ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>

    <div style="width:50%;" align="center" class="btn_margin">
        <button type="button" class="btn-info btn-xs" style="width:150px;height:30px;cursor:pointer"
                onclick="location.href='<?= $this->Url->build(['action' => 'add']) ?>'">新規登録</button>

        <input type="button" id="submitSortable" value="表示順序を変更"
               class="btn-default btn-xs" style="width:150px;height:30px;">
    </div>
</div>
