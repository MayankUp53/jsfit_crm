<?php
$search = $searchData ?? [];
$currentUser = $this->request->getAttribute('identity');
$userAuth = $currentUser ? ($currentUser->getOriginalData()->auth ?? null) : null;
$typeOfNumberOpt = ['営業所' => '営業所', '私用携帯' => '私用携帯', '固定電話' => '固定電話'];
$formatSmsText = static function ($text): array {
    $full = trim((string)$text);
    $display = $full;

    if (mb_strwidth($full) > 80) {
        $display = mb_strimwidth($full, 0, 80, '...');
    }

    return [$display, $full, $display !== $full];
};
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">SMS送信履歴検索条件</h3>
    </div>

    <?= $this->Form->create(null, ['url' => ['action' => 'index'], 'type' => 'post']) ?>
    <table class="table">
        <tbody>
            <tr>
                <td class="search-label">会社・支店名</td>
                <td>
                    <?= $this->Form->control('Search.company_name', [
                        'label' => false,
                        'value' => $search['company_name'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </td>

                <td class="search-label">氏名</td>
                <td>
                    <?= $this->Form->control('Search.name', [
                        'label' => false,
                        'value' => $search['name'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </td>

                <td class="search-label">電話番号</td>
                <td>
                    <?= $this->Form->control('Search.phone', [
                        'label' => false,
                        'value' => $search['phone'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </td>
            </tr>

            <tr>
                <td class="search-label">メール</td>
                <td>
                    <?= $this->Form->control('Search.email', [
                        'label' => false,
                        'value' => $search['email'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </td>

                <td class="search-label">得意先CD</td>
                <td>
                    <?= $this->Form->control('Search.client_cd', [
                        'label' => false,
                        'value' => $search['client_cd'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </td>

                <td class="search-label">FAX</td>
                <td>
                    <?= $this->Form->control('Search.fax', [
                        'label' => false,
                        'value' => $search['fax'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </td>
            </tr>

            <tr>
                <td class="search-label">ステータス</td>
                <td>
                    <?= $this->Form->control('Search.status', [
                        'label' => false,
                        'type' => 'select',
                        'multiple' => true,
                        'options' => $smsStatusOpt ?? [],
                        'value' => $search['status'] ?? [],
                        'class' => 'form-control',
                    ]) ?>
                </td>

                <td class="search-label">番号種別</td>
                <td>
                    <?= $this->Form->control('Search.type_of_number', [
                        'label' => false,
                        'type' => 'select',
                        'multiple' => true,
                        'options' => $typeOfNumberOpt,
                        'value' => $search['type_of_number'] ?? [],
                        'class' => 'form-control',
                    ]) ?>
                </td>

                <td class="search-label">送信日</td>
                <td>
                    <div class="d-flex align-items-center">
                        <?= $this->Form->control('Search.created_from', [
                            'label' => false,
                            'value' => $search['created_from'] ?? '',
                            'class' => 'form-control datepicker',
                            'placeholder' => '開始日',
                        ]) ?>
                        <span style="margin:0 5px;">~</span>
                        <?= $this->Form->control('Search.created_to', [
                            'label' => false,
                            'value' => $search['created_to'] ?? '',
                            'class' => 'form-control datepicker',
                            'placeholder' => '終了日',
                        ]) ?>
                    </div>
                </td>

            </tr>
        </tbody>
    </table>

    <div class="text-center btn_margin" style="width:95%;margin-bottom:5px;margin-top:5px;">
        <?= $this->Form->button('検　索', ['name' => 'searching', 'value' => '1', 'type' => 'submit', 'class' => 'btn-success btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?= $this->Form->button('クリア', ['name' => 'clear', 'value' => '1', 'type' => 'submit', 'class' => 'btn-success btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?php if ($userAuth !== null && $userAuth != 0): ?>
            <?= $this->Form->button('検索結果を出力', ['name' => 'download', 'value' => '1', 'type' => 'submit', 'class' => 'btn-success btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?php endif; ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">SMS送信履歴一覧</h3>
        <span style="float:right;margin-top:-20px">
            <?= $this->Paginator->counter(__('{{page}}/{{pages}}ページ  {{start}}件目から{{end}}件目 検索結果{{count}}件')) ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover" style="font-size:12px; width:100%;">
            <thead>
                <tr height="35" bgcolor="#20b2aa" style="vertical-align:middle;color:#ffffff;">
                    <th>得意先CD</th>
                    <th>会社・支店名</th>
                    <th>氏名</th>
                    <th>番号種別</th>
                    <th>TEL</th>
                    <th>FAX</th>
                    <th>SMS送信先</th>
                    <th>送信元</th>
                    <th width="28%">メッセージ</th>
                    <th>ステータス</th>
                    <th>送信日</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($smsHistories as $row): ?>
                    <?php [$smsDisplay, $smsFull, $smsHasTooltip] = $formatSmsText($row->sms_text); ?>
                    <tr style="cursor:pointer;"
                        onclick="window.location='<?= $this->Url->build(['controller' => 'SmsHistories', 'action' => 'view', $row->id]) ?>'">
                        <td><?= h($row->customer->client_cd ?? '') ?></td>
                        <td><?= h($row->customer->company_name ?? '') ?></td>
                        <td><?= h($row->customer->name ?? '') ?></td>
                        <td><?= h($row->customer->type_of_number ?? '') ?></td>
                        <td><?= h($row->customer->tel ?? '') ?></td>
                        <td><?= h($row->customer->fax ?? '') ?></td>
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
                        <td>
                            <a href="<?= $this->Url->build(['action' => 'view', $row->id]) ?>" class="btn btn-xs btn-default" onclick="event.stopPropagation();">詳細</a>
                            <?php if ($userAuth !== null && $userAuth != 0): ?>
                                <?= $this->Form->postLink('削除', ['action' => 'delete', $row->id], [
                                    'confirm' => __('SMS送信履歴を削除します。よろしいですか？'),
                                    'class' => 'btn btn-xs btn-danger',
                                    'style' => 'margin-left:6px;',
                                    'onclick' => 'event.stopPropagation();',
                                ]) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="paginateLinks btn_margin" style="width:100%" align="center">
    <ul class="pagination pagination-sm">
        <li><?= $this->Paginator->first('<< ' . __('最初')) ?></li>
        <li><?= $this->Paginator->prev('前へ ' . __('前')) ?></li>
        <li><?= $this->Paginator->numbers(['separator' => '']) ?></li>
        <li><?= $this->Paginator->next(__('次') . ' 次へ') ?></li>
        <li><?= $this->Paginator->last(__('最後') . ' >>') ?></li>
    </ul>
</div>

<style>
    .search-label {
        vertical-align: middle;
        color: #ffffff;
        background-color: #149c82;
    }
</style>

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
