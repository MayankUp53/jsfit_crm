<?php
$customer = $smsHistory->customer;
$smsFull = trim((string)$smsHistory->sms_text);
$smsDisplay = $smsFull;
$smsHasTooltip = false;

if (mb_strwidth($smsFull) > 160) {
    $smsDisplay = mb_strimwidth($smsFull, 0, 160, '...');
    $smsHasTooltip = true;
}
?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">顧客情報</h3>
    </div>

    <table class="table table-bordered">
        <tr>
            <th width="15%">得意先CD</th>
            <td><?= h($customer->client_cd ?? '') ?></td>
            <th width="15%">会社・支店名</th>
            <td><?= h($customer->company_name ?? '') ?></td>
        </tr>
        <tr>
            <th>氏名</th>
            <td><?= h($customer->name ?? '') ?></td>
            <th>番号種別</th>
            <td><?= h($customer->type_of_number ?? '') ?></td>
        </tr>
        <tr>
            <th>TEL</th>
            <td><?= h($customer->tel ?? '') ?></td>
            <th>FAX</th>
            <td><?= h($customer->fax ?? '') ?></td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td><?= h($customer->email ?? '') ?></td>
            <th>登録日</th>
            <td><?= h($customer->created?->format('Y-m-d')) ?></td>
        </tr>
    </table>

    <div class="text-right" style="margin-bottom:10px;margin-right:10px;">
        <a href="<?= $this->Url->build(['controller' => 'Customers', 'action' => 'edit', $customer->id]) ?>" class="btn btn-warning btn-xs">顧客編集</a>
        <a href="<?= $this->Url->build(['controller' => 'Contacts', 'action' => 'add', $customer->id]) ?>" class="btn btn-info btn-xs">対応履歴登録</a>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-default btn-xs">SMS履歴検索</a>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">SMS送信履歴詳細</h3>
    </div>

    <table class="table table-bordered">
        <tr>
            <th width="15%">ID</th>
            <td><?= h($smsHistory->id) ?></td>
            <th width="15%">ステータス</th>
            <td><?= h($smsStatusOpt[$smsHistory->status] ?? $smsHistory->status) ?></td>
        </tr>
        <tr>
            <th>SMS送信先</th>
            <td><?= h($smsHistory->phone) ?></td>
            <th>送信元</th>
            <td><?= h($smsHistory->send_sms_number) ?></td>
        </tr>
        <tr>
            <th>送信日</th>
            <td><?= $smsHistory->created ? h($smsHistory->created->format('Y-m-d H:i')) : '' ?></td>
            <th>更新日</th>
            <td><?= $smsHistory->updated ? h($smsHistory->updated->format('Y-m-d H:i')) : '' ?></td>
        </tr>
        <tr>
            <th>メッセージ</th>
            <td colspan="3">
                <?php if ($smsHasTooltip): ?>
                    <span class="sms-tooltip"
                          title="<?= h($smsFull) ?>"
                          data-toggle="tooltip"
                          data-bs-toggle="tooltip">
                        <?= nl2br(h($smsDisplay)) ?>
                    </span>
                <?php else: ?>
                    <?= nl2br(h($smsDisplay)) ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<?= $this->element('sms_history_panel', ['smsHistories' => $customerSmsHistories ?? []]) ?>

<?php $this->start('script'); ?>
<script>
    jQuery(function($) {
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('.sms-tooltip').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        } else if ($.fn.tooltip) {
            $('.sms-tooltip').tooltip();
        }
    });
</script>
<?php $this->end(); ?>
