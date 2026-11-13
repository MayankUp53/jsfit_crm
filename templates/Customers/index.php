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
        <h3 class="panel-title">顧客情報検索条件</h3>
    </div>
    <?= $this->Form->create(null, ['url' => ['action' => 'index'], 'type' => 'post']) ?>
    <table class="table customer-search">
    <colgroup><col style="width:10%"><col style="width:20%"><col style="width:10%"><col style="width:25%"><col style="width:10%"><col style="width:25%"></colgroup>
    <tbody>
    <tr>
        <td class="search-label">TEL</td>
        <td>
            <?= $this->Form->control('Search.tel_only', [
                'label' => false, 'type' => 'text',
                'value' => $search['tel_only'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">得意先名</td>
        <td>
            <?= $this->Form->control('Search.client_name', [
                'label' => false, 'type' => 'text',
                'value' => $search['client_name'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">得意先支店名</td>
        <td>
            <?= $this->Form->control('Search.client_branch_name', [
                'label' => false, 'type' => 'text',
                'value' => $search['client_branch_name'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>
    </tr>

    <tr>
        <td class="search-label">住所1</td>
        <td>
            <?= $this->Form->control('Search.addr1', [
                'label' => false, 'type' => 'text',
                'value' => $search['addr1'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">得意先CD</td>
        <td>
            <?= $this->Form->control('Search.client_code', [
                'label' => false, 'type' => 'text',
                'value' => $search['client_code'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">得意先支店CD</td>
        <td>
            <?= $this->Form->control('Search.client_branch_code', [
                'label' => false, 'type' => 'text',
                'value' => $search['client_branch_code'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>
    </tr>

    <tr>
        <td class="search-label">所属名</td>
        <td><?= $this->Form->control('Search.section_name', ['label' => false, 'type' => 'text', 'value' => $search['section_name'] ?? '', 'class' => 'form-control']) ?></td>
        <td class="search-label">営業担当者名</td>
        <td><?= $this->Form->control('Search.sales_person', ['label' => false, 'type' => 'text', 'value' => $search['sales_person'] ?? '', 'class' => 'form-control']) ?></td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="search-label">顧客の担当者1</td>
        <td><?= $this->Form->control('Search.incharge1_name', ['label' => false, 'type' => 'text', 'value' => $search['incharge1_name'] ?? '', 'class' => 'form-control']) ?></td>
        <td class="search-label">顧客の担当者1電話</td>
        <td><?= $this->Form->control('Search.incharge1_phone', ['label' => false, 'type' => 'text', 'value' => $search['incharge1_phone'] ?? '', 'class' => 'form-control']) ?></td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="search-label">顧客の担当者2</td>
        <td><?= $this->Form->control('Search.incharge2_name', ['label' => false, 'type' => 'text', 'value' => $search['incharge2_name'] ?? '', 'class' => 'form-control']) ?></td>
        <td class="search-label">顧客の担当者2電話</td>
        <td><?= $this->Form->control('Search.incharge2_phone', ['label' => false, 'type' => 'text', 'value' => $search['incharge2_phone'] ?? '', 'class' => 'form-control']) ?></td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="search-label">登録日（From）</td>
        <td>
            <?= $this->Form->control('Search.created_from', ['label' => false, 'type' => 'text', 'value' => $search['created_from'] ?? '', 'class' => 'form-control datepicker']) ?>
        </td>
        <td class="search-label">登録日（To）</td>
        <td>
            <?= $this->Form->control('Search.created_to', ['label' => false, 'type' => 'text', 'value' => $search['created_to'] ?? '', 'class' => 'form-control datepicker']) ?>
        </td>
        <td></td><td></td>
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
                    <td width="4%">得意先CD</td>
                    <td width="6%">得意先支店CD</td>
                    <td width="6%">得意先名</td>
                    <td width="6%">得意先支店名</td>
                    <td width="5%">TEL</td>
                    <td width="7%">サブ電話番号1</td>
                    <td width="8%">住所1</td>
                    <td width="6%">顧客の担当者1</td>
                    <td width="6%">顧客の担当者1電話</td>
                    <td width="6%">顧客の担当者2</td>
                    <td width="6%">顧客の担当者2電話</td>
                    <td width="5%">所属名</td>
                    <td width="5%">営業担当者名</td>
                    <td width="5%">備考１</td>
                    <td width="5%">登録日</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr onDblClick="location.href='<?= $this->Url->build(['controller' => 'Contacts', 'action' => 'add', $customer->id]) ?>'">
                        <td><?= h($customer->client_code ?? $customer->client_cd) ?></td>
                        <td><?= h($customer->client_branch_code) ?></td>
                        <td><?= h($customer->client_name ?? $customer->company_name ?? $customer->name) ?></td>
                        <td><?= h($customer->client_branch_name ?? $customer->company_branch_name) ?></td>
                        <td><?= h($customer->tel) ?></td>
                        <td><?= h($customer->tel2) ?></td>
                        <td><a style="color:blue" target="_blank" rel="noopener noreferrer" href="https://www.google.co.jp/maps?q=<?= rawurlencode((string)$customer->addr1) ?>"><?= h($customer->addr1) ?></a></td>
                        <td><?= h($customer->incharge1_name) ?></td>
                        <td><?= h($customer->incharge1_phone) ?></td>
                        <td><?= h($customer->incharge2_name) ?></td>
                        <td><?= h($customer->incharge2_phone) ?></td>
                        <td><?= h($customer->section_name) ?></td>
                        <td><?= h($customer->sales_person) ?></td>
                        <td><?= h(mb_substr((string)$customer->notes1, 0, 11) . (mb_strlen((string)$customer->notes1) > 11 ? '...' : '')) ?></td>
                        <td><?= $customer->created ? h($customer->created->format('Y-m-d')) : '' ?></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
    <div class="paginateLinks btn_margin" style="width:100%" align="center">
        <ul class="pagination pagination-sm">
            <li><?= $this->Paginator->first('<< ', ['class' => 'page-link']) ?></li>
            <li><?= $this->Paginator->prev('前へ ', ['class' => 'page-link']) ?></li>
            <li><?= $this->Paginator->numbers(['separator' => '']) ?></li>
            <li><?= $this->Paginator->next('次へ', ['class' => 'page-link']) ?></li>
            <li><?= $this->Paginator->last('>>', ['class' => 'page-link']) ?></li>
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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && jQuery.fn.datepicker) {
            jQuery('.customer-search .datepicker').datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
                dateFormat: 'yy-mm-dd'
            });
        }
    });
</script>
