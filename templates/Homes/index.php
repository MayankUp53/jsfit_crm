<?php
/**
 * Homes/index
 * AmeyoJ Dashboard (Manual Dial + Call History)
 */
?>

<div class="bs-docs-section clearfix">

<!-- ================= DASHBOARD ================= -->
<div class="panel panel-primary filterable">
    <div class="panel-heading">
        <h3 class="panel-title">AmeyoJ ダッシュボード</h3>
    </div>

<?php if (!empty($cidOpt)): ?>

<!-- ===================================================== -->
<!-- JAVASCRIPT VALIDATION + PHONE BOOK HANDLER -->
<!-- ===================================================== -->
<script>
function validateManualDial() {
    const cid = document.getElementById('cid')?.value;
    const tel = document.getElementById('tel')?.value;

    if (!cid) {
        alert('通知番号を選択してください');
        return false;
    }
    if (!tel) {
        alert('発信先電話番号を入力してください');
        return false;
    }
    return true;
}

</script>

<!-- ===================================================== -->
<!-- MANUAL DIAL FORM -->
<!-- ===================================================== -->
<?php if (!empty($cidOpt)): ?>
<?= $this->Form->create(null, [
    'url' => ['controller' => 'Homes', 'action' => 'manualDialApi'],
    'class' => 'form-inline',
    'onsubmit' => 'return validateManualDial();'
]) ?>

<table class="table" style="width:auto; margin-bottom:10px;">
    <tr>
        <td style="width:180px;">
            <?= $this->Form->control('cid', [
                'label' => false,
                'id' => 'cid',
                'type' => 'select',
                'options' => $cidOpt,
                'empty' => '--- 通知番号 ---',
                'class' => 'form-control',
            ]) ?>
        </td>

        <td style="width:180px;">
            <?= $this->Form->control('tel', [
                'label' => false,
                'id' => 'tel',
                'type' => 'text',
                'placeholder' => '発信先TEL',
                'class' => 'form-control',
            ]) ?>
        </td>

        <td style="padding-left:6px;">
            <button type="button"
                class="btn btn-success btn-xs"
                onclick="window.open('<?= $this->Url->build(['action' => 'phoneList', '?' => ['target' => 'tel']]) ?>','','width=500,height=700');">
                電話帳
            </button>
        </td>

        <td style="padding-left:6px;">
            <?= $this->Form->submit('発信', [
                'class' => 'btn btn-info btn-xs',
            ]) ?>
        </td>

    </tr>
</table>

<?= $this->Form->end() ?>
<?php endif; ?>

<?php endif; ?>

</div>


<!-- ===================================================== -->
<!-- CALL HISTORY -->
<!-- ===================================================== -->
<div class="panel-heading" style="background-color:midnightblue;color:#fff">
    コール履歴一覧（直近<?= h($limit) ?>件まで表示）
</div>

<table id="callsTable" class="table table-hover table-bordered" style="font-size:12px; width:100%;">

<thead>
<tr style="color:#fff;background:#46b8da;">
    <th>No.</th>
    <th>キャンペーン</th>
    <th>種別</th>
    <th>キュー名</th>
    <th>発信元</th>
    <th>発信先</th>
    <th>コール結果</th>
    <th>転送先</th>
    <th>ユーザ名</th>
    <th>開始日時</th>
    <th>通話時間</th>
    <th>通録</th>
</tr>
</thead>

<tbody>
<?php $calls = $response ?? []; ?>

<?php if (!empty($calls)): ?>
    <?php foreach ($calls as $row): ?>
        <tr>
            <td><?= h($row['row_number'] ?? '') ?></td>
            <td><?= h($row['campaign_name'] ?? '') ?></td>
            <td><?= h($row['call_type'] ?? '') ?></td>
            <td><?= h($row['agent_queue_name'] ?? '') ?></td>
            <td><?= h($row['srcphone'] ?? '') ?></td>
            <td><?= h($row['dstphone'] ?? '') ?></td>
            <td><?= h($row['disposition_code'] ?? '') ?></td>
            <td><?= h($row['transfer_to'] ?? '') ?></td>
            <td><?= h($row['user_name'] ?? '') ?></td>
            <td><?= h(isset($row['call_originate_time']) ? substr($row['call_originate_time'],0,16) : '') ?></td>
            <td><?= h($row['talk_time'] ?? '') ?></td>
            <td>
                <?php if (!empty($row['call_id'])): ?>
                    <a href="#">再生</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="12">データなし</td></tr>
<?php endif; ?>
</tbody>

</table>

</div>


<!-- ===================================================== -->
<!-- DATATABLE -->
<!-- ===================================================== -->
<?php $this->start('script'); ?>
<script>
jQuery(function($){
    $('#callsTable').DataTable({
        pageLength: 25,
        lengthChange: false,
        ordering: true,
        responsive: true,
         dom: '<"row"<"col-sm-6"f><"col-sm-6"l>>t<"row"<"col-sm-6"i><"col-sm-6"p>>'
    });
});
</script>
<?php $this->end(); ?>
