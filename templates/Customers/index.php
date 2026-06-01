<?php

/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface<\App\Model\Entity\Customer> $customers
 */
$currentUser = $this->request->getAttribute('identity');
$search = $searchData ?? [];
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">顧客情報検索条件 </h3>
    </div>
    <?= $this->Form->create(null, ['url' => ['action' => 'index'], 'type' => 'post']) ?>
    <table class="table">
       <tbody>
    <!-- First Row: Company Name, Name, Phone -->
    <tr>
        <td class="search-label">会社・支店名</td>
        <td>
            <?= $this->Form->control('Search.company_name', [
                'label' => false,
                'value' => $search['company_name'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">氏名</td>
        <td>
            <?= $this->Form->control('Search.name', [
                'label' => false,
                'value' => $search['name'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">電話番号</td>
        <td>
            <?= $this->Form->control('Search.phone', [
                'label' => false,
                'value' => $search['phone'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>
    </tr>

    <!-- Second Row: Email, Client CD, FAX -->
    <tr>
        <td class="search-label">メール</td>
        <td>
            <?= $this->Form->control('Search.email', [
                'label' => false,
                'value' => $search['email'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">得意先CD</td>
        <td>
            <?= $this->Form->control('Search.client_cd', [
                'label' => false,
                'value' => $search['client_cd'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">FAX</td>
        <td>
            <?= $this->Form->control('Search.fax', [
                'label' => false,
                'value' => $search['fax'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>
    </tr>

    <!-- Third Row: Created Date, Updated Date, Type of Number -->
    <tr>
        <td class="search-label">登録日</td>
        <td>
            <div class="d-flex align-items-center">
                <?= $this->Form->control('Search.created_from', [
                    'label' => false,
                    'value' => $search['created_from'] ?? '',
                    'class' => 'form-control datepicker',
                    'placeholder' => '開始日'
                ]) ?>
                <span style="margin: 0 5px;">~</span>
                <?= $this->Form->control('Search.created_to', [
                    'label' => false,
                    'value' => $search['created_to'] ?? '',
                    'class' => 'form-control datepicker',
                    'placeholder' => '終了日'
                ]) ?>
            </div>
        </td>

        <td class="search-label">更新日</td>
        <td>
            <div class="d-flex align-items-center">
                <?= $this->Form->control('Search.updated_from', [
                    'label' => false,
                    'value' => $search['updated_from'] ?? '',
                    'class' => 'form-control datepicker',
                    'placeholder' => '開始日'
                ]) ?>
                <span style="margin: 0 5px;">~</span>
                <?= $this->Form->control('Search.updated_to', [
                    'label' => false,
                    'value' => $search['updated_to'] ?? '',
                    'class' => 'form-control datepicker',
                    'placeholder' => '終了日'
                ]) ?>
            </div>
        </td>

        <td class="search-label">番号種別</td>
        <td>
            <?= $this->Form->control('Search.type_of_number', [
                'label' => false,
                'type' => 'select',
                'multiple' => true,
                'options' => ['営業所' => '営業所', '私用携帯' => '私用携帯', '固定電話' => '固定電話'],
                'class' => 'form-control',
                'value' => $search['type_of_number'] ?? []
            ]) ?>
        </td>
    </tr>
</tbody>


    </table>
    <div class="text-center btn_margin" style="width:95%;margin-bottom:5px;margin-top:5px;">
        <?= $this->Form->button('検　索', ['name' => 'searching', 'value' => '1', 'type' => 'submit', 'class' => 'btn-success btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?= $this->Form->button('クリア', ['name' => 'clear', 'value' => '1', 'type' => 'submit', 'class' => 'btn-success btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?= $this->Form->button('新規登録', ['name' => 'add', 'value' => '1', 'type' => 'submit', 'class' => 'btn-info btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?php if ($currentUser && $currentUser->auth != 0): ?>
            <?= $this->Form->button('検索結果を出力', ['name' => 'download', 'value' => '1', 'type' => 'submit', 'class' => 'btn-success btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?php endif; ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">検索結果一覧</h3>
        <span style="float:right;margin-top:-20px">
            <?= $this->Paginator->counter(__('{{page}}/{{pages}}ページ  {{start}}件目から{{end}}件目 検索結果{{count}}件')) ?>
        </span>
    </div>
    <div class="table-responsive">
        <table id="dt" class="table table-striped table-hover" style="font-size:12px">
            <thead>
                <tr height="35" bgcolor="#20b2aa" style="vertical-align:middle;color:#ffffff;">
                    <td width="8%">得意先CD</td>
                    <td width="15%">会社・支店名</td>
                    <td width="12%">氏名</td>
                    <td width="10%">番号種別</td>
                    <td width="10%">TEL</td>
                    <td width="10%">FAX</td>
                    <td width="10%">メモ</td>
                    <td width="15%">メールアドレス</td>
                    <td width="10%">登録日</td>
                    <td width="10%">更新日</td>
                    <td width="10%">操作</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr onDblClick="location.href='<?= $this->Url->build(['controller' => 'Contacts', 'action' => 'add', $customer->id]) ?>'">
                        <td><?= h($customer->client_cd) ?></td>
                        <td><?= h($customer->company_name) ?></td>
                        <td><?= h($customer->name) ?></td>
                        <td><?= h($customer->type_of_number) ?></td>
                        <td>
                            <?php if (!empty($cidOpt)): ?>
                                <!-- Clickable number -->
                                <span class="tel-link"
                                    style="color:#007bff; text-decoration:underline; cursor:pointer;"
                                    data-tel="<?= h($customer->tel) ?>"
                                    data-name="<?= h($customer->name) ?>">
                                    <?= h($customer->tel) ?>
                                </span>
                            <?php else: ?>
                                <!-- Plain text -->
                                <?= h($customer->tel) ?>
                            <?php endif; ?>
                        </td>



                        <td><?= h($customer->fax ?? '') ?></td>
                        <td><?= h($customer->memo ?? '') ?></td>
                        <td>
                            <?php
                            $rawEmail = (string)($customer->email ?? '');
                            $emailParts = array_values(array_filter(array_map('trim', preg_split('/\s*[,;\r\n]+\s*/', $rawEmail))));
                            $emailCount = count($emailParts);
                            $emailFull = implode('; ', $emailParts);
                            $emailDisplay = $emailFull;

                            if ($emailCount > 2) {
                                $emailDisplay = implode('; ', array_slice($emailParts, 0, 2)) . ' ...';
                            }
                            ?>
                            <?php if ($emailCount > 2): ?>
                                <span class="email-tooltip"
                                      title="<?= h($emailFull) ?>"
                                      data-toggle="tooltip"
                                      data-bs-toggle="tooltip">
                                    <?= h($emailDisplay) ?>
                                </span>
                            <?php else: ?>
                                <?= h($emailDisplay) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $customer->created ? h($customer->created->format('Y-m-d H:i')) : '' ?></td>
                        <td><?= $customer->updated ? h($customer->updated->format('Y-m-d H:i')) : '' ?></td>
                        <td>
                            <a href="<?= $this->Url->build(['action' => 'edit', $customer->id]) ?>" class="btn btn-xs btn-primary">編集</a>
                            <?php if ($currentUser && $currentUser->getOriginalData()->auth != "0"): ?>
                                <?= $this->Form->postLink('削除', ['action' => 'delete', $customer->id], [
                                    'confirm' => __('顧客情報を削除します。よろしいですか？'),
                                    'class' => 'btn btn-xs btn-danger',
                                    'style' => 'margin-left:10px;',
                                ]) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
    <div class="paginateLinks btn_margin" style="width:100%" align="center">
        <ul class="pagination pagination-sm">
            <li><?= $this->Paginator->first('<< ' . __('最初'), ['class' => 'page-link']) ?></li>
            <li><?= $this->Paginator->prev('前へ ' . __('前'), ['class' => 'page-link']) ?></li>
            <li><?= $this->Paginator->numbers(['separator' => '']) ?></li>
            <li><?= $this->Paginator->next(__('次') . ' 次へ', ['class' => 'page-link']) ?></li>
            <li><?= $this->Paginator->last(__('最後') . ' >>', ['class' => 'page-link']) ?></li>
        </ul>
    </div>
    <br><br>
</div>

<style>
    .search-label {
        vertical-align: middle;
        color: #ffffff;
        background-color: #149c82;
    }
    .tooltip .tooltip-inner,
    .bs-tooltip-top .tooltip-inner,
    .bs-tooltip-bottom .tooltip-inner,
    .bs-tooltip-left .tooltip-inner,
    .bs-tooltip-right .tooltip-inner {
        box-shadow: 0 4px 12px rgba(201, 200, 200, 0.05);
    }
</style>

<!-- Popup Modal -->
<div id="callModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background: rgba(185, 184, 184, 0.5); justify-content:center; align-items:center; z-index:9999;">

    <div style="background:white; padding:30px 25px; border-radius:12px; width:350px; 
        box-shadow:0 8px 25px rgba(158, 157, 157, 0.3); text-align:center; font-family:Arial, sans-serif; max-height:90vh; overflow:auto;">

        <h2 style="margin-top:0; font-size:20px; color:#333; margin-bottom:20px;">通話する</h2>

        <?= $this->Form->create(null, [
            'url' => ['controller' => 'Homes', 'action' => 'manualDialApi'],
            'type' => 'post',
            'id' => 'callForm'
        ]) ?>

        <!-- Hidden input for phone number -->
        <?= $this->Form->hidden('tel', ['id' => 'modalTel']) ?>

        <!-- Name & Number Table Row -->
        <div style="margin-bottom:20px;">
            <table style="width:100%; font-size:16px; border-collapse:collapse;">
                <tr>
                    <td style="font-weight:bold; color:#555; width:30%;">氏名</td>
                    <td id="displayName" style="color:#007bff;"></td>
                </tr>
                <tr>
                    <td style="font-weight:bold; color:#555;">番号</td>
                    <td id="displayTel" style="color:#007bff;"></td>
                </tr>
            </table>
        </div>

        <!-- CID Custom Scrollable Dropdown -->
        <div style="margin-bottom:25px; text-align:left;">
            <label for="cidSelect" style="display:block; font-weight:bold; margin-bottom:5px; color:#555;">発信者ID (CID)</label>

            <?= $this->Form->control('cid', [
                'type' => 'select',
                'id' => 'cidSelect',
                'options' => $cidOpt,
                'empty' => 'CIDを選択',
                'label' => false,
                'style' => 'width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; font-size:14px; max-height:150px; overflow-y:auto;'
            ]) ?>
        </div>

        <!-- Buttons -->
        <div style="display:flex; gap:15px; justify-content:center;">
            <?= $this->Form->button('通話', [
                'type' => 'submit',
                'style' => 'flex:1; padding:12px; background:#28a745; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; font-size:15px; transition:0.3s;',
                'onmouseover' => "this.style.background='#218838'",
                'onmouseout' => "this.style.background='#28a745'"
            ]) ?>
            <button type="button" id="cancelBtn"
                style="flex:1; padding:12px; background:#f0f0f0; color:#333; border:none; border-radius:8px; cursor:pointer; font-weight:bold; font-size:15px; transition:0.3s;"
                onmouseover="this.style.background='#e0e0e0'"
                onmouseout="this.style.background='#f0f0f0'">
                キャンセル
            </button>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<!-- JS for popup -->
<!-- JS for popup / direct call -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('callModal');
        const displayTel = document.getElementById('displayTel');
        const modalTel = document.getElementById('modalTel');
        const cancelBtn = document.getElementById('cancelBtn');
        const displayName = document.getElementById('displayName');
        const callForm = document.getElementById('callForm');
        const cidSelect = document.getElementById('cidSelect');

        // Count of CID options (excluding placeholder)
        const cidCount = cidSelect.options.length - 1;

        // Ensure modal is hidden initially
        modal.style.display = 'none';

        // Handle click on phone numbers
        document.querySelectorAll('.tel-link').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                let tel = this.dataset.tel || '';
                let cleanTel = tel.replace(/-/g, '');
                let name = this.dataset.name || '';

                if (cidCount === 1) {
                    // Only one CID option, direct call
                    modalTel.value = cleanTel;
                    cidSelect.selectedIndex = 1; // Select the single CID
                    callForm.submit();
                } else {
                    // Multiple CID options, open modal
                    displayTel.textContent = cleanTel;
                    modalTel.value = cleanTel;
                    displayName.textContent = name || '名前なし';
                    modal.style.display = 'flex';
                }
            });
        });

        // Close modal when clicking Cancel button
        cancelBtn.addEventListener('click', () => modal.style.display = 'none');

        // Close modal if clicking outside the popup
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Initialize tooltips if Bootstrap is available; otherwise native title will work
        document.querySelectorAll('.email-tooltip').forEach(el => {
            if (window.bootstrap && bootstrap.Tooltip) {
                new bootstrap.Tooltip(el);
            } else if (window.jQuery && typeof window.jQuery(el).tooltip === 'function') {
                window.jQuery(el).tooltip();
            }
        });

        // Auto-hide tooltips on scroll/navigation/visibility changes
        const hideAllTooltips = () => {
            if (window.bootstrap && bootstrap.Tooltip) {
                document.querySelectorAll('.email-tooltip').forEach(el => {
                    const instance = bootstrap.Tooltip.getInstance(el);
                    if (instance) {
                        instance.hide();
                    }
                });
            } else if (window.jQuery && typeof window.jQuery.fn.tooltip === 'function') {
                window.jQuery('.email-tooltip').tooltip('hide');
            }
        };

        window.addEventListener('scroll', hideAllTooltips, true);
        window.addEventListener('pagehide', hideAllTooltips);
        window.addEventListener('pageshow', hideAllTooltips);
        window.addEventListener('beforeunload', hideAllTooltips);
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                hideAllTooltips();
            }
        });
        document.addEventListener('click', (e) => {
            const target = e.target;
            if (target && target.closest('a, button, form')) {
                hideAllTooltips();
            }
        }, true);
    });
</script>
