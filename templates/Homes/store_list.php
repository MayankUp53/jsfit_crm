<?php
/**
 * Store list pop-up (reuses phone list data but only shows names).
 */
?>
<?php $this->viewBuilder()->setLayout(''); ?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">店舗一覧</h3>
    </div>
    <ul class="list-group">
        <?php foreach ($mtPhonelists as $row): ?>
            <li class="list-group-item"><?= h($row->item_name) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
