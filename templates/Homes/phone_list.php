<?php
/**
 * Simple phone book pop-up.
 */
?>
<?php $this->viewBuilder()->setLayout(''); ?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">電話帳</h3>
    </div>
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>名称</th>
                <th>番号</th>
                <th>備考</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mtPhonelists as $row): ?>
                <tr>
                    <td><?= h($row->item_name) ?></td>
                    <td><?= h($row->phone) ?></td>
                    <td><?= h($row->item_memo) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
