<?php
/**
 * Homes/index
 * AmeyoJ Dashboard (Manual Dial + SMS + Call History)
 */
?>

<div class="bs-docs-section clearfix">

<!-- ================= DASHBOARD ================= -->
<div class="panel panel-primary filterable">
    <div class="panel-heading">
        <h3 class="panel-title">AmeyoJ ダッシュボード</h3>
    </div>

<?php if (!empty($cidOpt) || !empty($smsCidOpt)): ?>

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

function validateSms() {
    const smsCidField = document.getElementById('sms_cid');
    const smsCid = smsCidField?.value;
    const smsTel = document.getElementById('sms_tel')?.value;
    const smsText = document.getElementById('sms_text')?.value;
    const recipients = smsTel
        ? smsTel.split(/[\s,;]+/).map(function (tel) { return tel.trim(); }).filter(Boolean)
        : [];

    if (smsCidField && smsCidField.tagName === 'SELECT' && !smsCid) {
        alert('SMS通知番号を選択してください');
        return false;
    }
    if (recipients.length === 0) {
        alert('SMS送信先電話番号を入力してください');
        return false;
    }
    if (!smsText) {
        alert('SMSメッセージを入力してください');
        return false;
    }

    return confirm(recipients.length + '件の送信先にSMS送信しますか？');
}

function resetSmsModalFields() {
    const smsCid = document.getElementById('sms_cid');
    const smsTel = document.getElementById('sms_tel');
    const smsText = document.getElementById('sms_text');

    if (smsCid && smsCid.tagName === 'SELECT') {
        smsCid.value = '';
    }
    if (smsTel) {
        smsTel.value = '';
    }
    if (smsText) {
        smsText.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery) {
        jQuery('#sendSmsModal').on('show.bs.modal hidden.bs.modal', resetSmsModalFields);
    }

    const sendSmsModal = document.getElementById('sendSmsModal');
    if (sendSmsModal) {
        sendSmsModal.querySelectorAll('[data-dismiss="modal"]').forEach(function (button) {
            button.addEventListener('click', resetSmsModalFields);
        });
    }
});
</script>

<style>
.send-sms-fields td {
    display: block;
    width: 100% !important;
    padding: 0 0 12px 0 !important;
}
.send-sms-fields label {
    display: block;
    margin-bottom: 4px;
}
.send-sms-fields .form-control {
    width: 100%;
}
</style>

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

        <?php if (!empty($smsCidOpt)): ?>
        <td style="padding-left:6px;">
            <button type="button"
                class="btn btn-warning btn-xs"
                data-toggle="modal"
                data-target="#sendSmsModal">
                SMS送信
            </button>
        </td>
        <?php endif; ?>
    </tr>
</table>

<?= $this->Form->end() ?>
<?php elseif (!empty($smsCidOpt)): ?>
<table class="table" style="width:auto; margin-bottom:10px;">
    <tr>
        <td style="padding-left:6px;">
            <button type="button"
                class="btn btn-warning btn-xs"
                data-toggle="modal"
                data-target="#sendSmsModal">
                SMS送信
            </button>
        </td>
    </tr>
</table>
<?php endif; ?>

<!-- ===================================================== -->
<!-- SMS MODAL -->
<!-- ===================================================== -->
<?php if (!empty($smsCidOpt)): ?>

<?= $this->Form->create(null, [
    'url' => ['controller' => 'Homes', 'action' => 'sendSmsApi'],
    'onsubmit' => 'return validateSms();'
]) ?>

<div class="modal fade" id="sendSmsModal" tabindex="-1" role="dialog" aria-labelledby="sendSmsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#20b2aa;color:#fff;">
                <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="sendSmsModalLabel">SMS送信</h4>
            </div>
            <div class="modal-body">

<table class="send-sms-fields" style="width:100%; margin:0; border-collapse:collapse;">
    <tr>
        <td style="width:180px;">
            <label for="sms_cid">SMS通知番号</label>
            <?php if (count($smsCidOpt) === 1): ?>
                <?php $onlySmsCid = array_key_first($smsCidOpt); ?>
                <?= $this->Form->hidden('sms_cid', [
                    'id' => 'sms_cid',
                    'value' => $onlySmsCid,
                ]) ?>
                <div class="form-control" style="height:auto;min-height:34px;background-color:#f5f5f5;">
                    <?= h($smsCidOpt[$onlySmsCid]) ?>
                </div>
            <?php else: ?>
            <?= $this->Form->control('sms_cid', [
                'label' => false,
                'id' => 'sms_cid',
                'type' => 'select',
                'options' => $smsCidOpt,
                'empty' => '--- SMS通知番号 ---',
                'class' => 'form-control',
            ]) ?>
            <?php endif; ?>
        </td>

        <td style="width:180px;padding-left:10px;">
            <label for="sms_tel">SMS送信先番号</label>
            <?= $this->Form->control('sms_tel', [
                'label' => false,
                'id' => 'sms_tel',
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => '1行に1番号、またはカンマ区切りで入力',
                'class' => 'form-control',
                'style' => 'resize:vertical;',
            ]) ?>
        </td>

        <td style="padding-left:6px;">
            <button type="button"
                class="btn btn-warning btn-xs"
                onclick="window.open('<?= $this->Url->build(['action' => 'phoneList', '?' => ['target' => 'sms_tel', 'multi' => 1]]) ?>','','width=650,height=700');">
                電話帳
            </button>
        </td>

     <td style="width: 120px; padding-left:6px;">
    <label for="sms_text">メッセージ</label>
    <?= $this->Form->control('sms_text', [
        'label' => false,
        'id' => 'sms_text',
        'type' => 'textarea',
        'rows' => 3,
        'placeholder' => 'SMSメッセージを入力',
        'class' => 'form-control',
        'style' => 'resize:none;',
    ]) ?>
</td>
        <td style="padding-left:6px;">
            <button type="button"
                class="btn btn-warning btn-xs"
                onclick="window.open('<?= $this->Url->build(['action' => 'smsTextList', '?' => ['target' => 'sms_text']]) ?>','','width=750,height=700');">
                SMS文面
            </button>
        </td>
        <td style="padding-left:6px;">
            <?= $this->Form->submit('SMS送信', [
                'class' => 'btn btn-danger',
            ]) ?>
        </td>
    </tr>
</table>

            </div>
        </div>
    </div>
</div>

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
