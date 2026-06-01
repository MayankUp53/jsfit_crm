<?php
$smsHistories = $smsHistories ?? [];
$smsStatusOpt = [
    'success' => '成功',
    'failed' => '失敗',
];

$formatSmsText = static function ($text): array {
    $full = trim((string)$text);
    $display = $full;

    if (mb_strwidth($full) > 80) {
        $display = mb_strimwidth($full, 0, 80, '...');
    }

    return [$display, $full, $display !== $full];
};
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">SMS送信履歴</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover" style="font-size:12px">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>送信先番号</th>
                    <th>送信元番号</th>
                    <th>メッセージ</th>
                    <th>ステータス</th>
                    <th>送信日時</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($smsHistories) && count($smsHistories) > 0): ?>
                    <?php foreach ($smsHistories as $row): ?>
                        <?php [$smsDisplay, $smsFull, $smsHasTooltip] = $formatSmsText($row->sms_text); ?>
                        <tr style="cursor:pointer;"
                            onclick="window.location='<?= $this->Url->build(['controller' => 'SmsHistories', 'action' => 'view', $row->id]) ?>'">
                            <td><?= h($row->id) ?></td>
                            <td><?= h($row->phone) ?></td>
                            <td><?= h($row->send_sms_number) ?></td>
                            <td>
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
                            <td><?= h($smsStatusOpt[$row->status] ?? $row->status) ?></td>
                            <td><?= $row->created ? h($row->created->format('Y-m-d H:i')) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">SMS送信履歴はありません。</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('.sms-tooltip').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        } else if (window.jQuery && typeof window.jQuery.fn.tooltip === 'function') {
            window.jQuery('.sms-tooltip').tooltip();
        }
    });
</script>
