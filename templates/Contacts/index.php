<?php
$search = $searchData ?? [];
$currentUser = $this->request->getAttribute('identity');
$userAuth = $currentUser ? ($currentUser->getOriginalData()->auth ?? null) : null;
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴検索条件</h3>
    </div>
    <?= $this->Form->create(null, ['url' => ['action' => 'index'], 'type' => 'post']) ?>
    <table class="table">
      <tbody>

    <!-- Legacy history search fields -->
    <tr>
        <td class="search-label">TEL</td>
        <td>
            <?= $this->Form->control('Search.tel', [
                'label' => false,
                'value' => $search['tel'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">得意先名</td>
        <td>
            <?= $this->Form->control('Search.client_name', [
                'label' => false,
                'value' => $search['client_name'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>

        <td class="search-label">得意先支店名</td>
        <td>
            <?= $this->Form->control('Search.client_branch_name', [
                'label' => false,
                'value' => $search['client_branch_name'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>
    </tr>

    <!-- Address and OP -->
    <tr>
        <td class="search-label">住所</td>
        <td>
            <?= $this->Form->control('Search.addr', [
                'label' => false,
                'type' => 'text',
                'value' => $search['addr'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>
        <td class="search-label" rowspan="3">担当OP</td>
        <td rowspan="3">
            <?= $this->Form->control('Search.created_user_id', [
                'label' => false,
                'type' => 'select',
                'multiple' => true,
                'options' => $opeOpt ?? $allUsersOpt ?? [],
                'value' => $search['created_user_id'] ?? [],
                'class' => 'form-control',
                'size' => 6,
            ]) ?>
        </td>
        <td class="search-label" rowspan="3">ステータス</td>
        <td rowspan="3">
            <?= $this->Form->control('Search.status', [
                'label' => false,
                'type' => 'select',
                'multiple' => true,
                'options' => $statusOpt ?? [],
                'value' => $search['status'] ?? [],
                'class' => 'form-control'
            ]) ?>
        </td>
    </tr>
    <tr>
        <td class="search-label">概要</td>
        <td>
            <?= $this->Form->control('Search.summary', [
                'label' => false,
                'value' => $search['summary'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>
    </tr>
    <tr>
        <td class="search-label">対応内容</td>
        <td>
            <?= $this->Form->control('Search.detail', [
                'label' => false,
                'value' => $search['detail'] ?? '',
                'class' => 'form-control'
            ]) ?>
        </td>
    </tr>
    <tr>
        <td class="search-label">登録日（From）</td>
        <td><?= $this->Form->control('Search.created_from', ['label' => false, 'value' => $search['created_from'] ?? '', 'class' => 'form-control datepicker']) ?></td>
        <td class="search-label">更新日（From）</td>
        <td><?= $this->Form->control('Search.updated_from', ['label' => false, 'value' => $search['updated_from'] ?? '', 'class' => 'form-control datepicker']) ?></td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="search-label">登録日（To）</td>
        <td><?= $this->Form->control('Search.created_to', ['label' => false, 'value' => $search['created_to'] ?? '', 'class' => 'form-control datepicker']) ?></td>
        <td class="search-label">更新日（To）</td>
        <td><?= $this->Form->control('Search.updated_to', ['label' => false, 'value' => $search['updated_to'] ?? '', 'class' => 'form-control datepicker']) ?></td>
        <td></td><td></td>
    </tr>

</tbody>

    </table>


    <div class="text-center btn_margin" style="width:95%;margin-bottom:5px;margin-top:5px;">
        <?= $this->Form->button('検　索', ['name' => 'searching', 'value' => '1', 'type' => 'submit', 'class' => 'btn-success btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?= $this->Form->button('クリア', ['name' => 'clear', 'value' => '1', 'type' => 'submit', 'class' => 'btn-success btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?= $this->Form->button('新規登録', ['name' => 'add', 'value' => '1', 'type' => 'submit', 'class' => 'btn-info btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
        <?php if ($userAuth !== null && $userAuth != 0): ?>
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
        <table id="dt" class="table table-striped table-hover" style="font-size:12px; width:100%;">

            <thead>
                <tr height="35" bgcolor="#20b2aa" style="vertical-align:middle;color:#ffffff;">
                    <th width="5%">得意先CD</th>
                    <th width="5%">得意先支店CD</th>
                    <th width="7%">TEL</th>
                    <th width="10%">得意先名</th>
                    <th width="10%">得意先支店名</th>
                    <th width="10%">営業担当者名</th>
                    <th width="10%">概要</th>
                    <th width="8%">ステータス</th>
                    <th width="12%">住所</th>
                    <th width="8%">担当</th>
                    <th width="8%">登録日</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($contacts as $row): ?>
                    <tr ondblclick="window.location='<?= $this->Url->build([
                                                            'controller' => 'Contacts',
                                                            'action' => 'view',
                                                            $row->id ?? ''
                                                        ]) ?>'">

                        <td><?= h($row->customer->client_code ?? $row->customer->client_cd ?? '') ?></td>
                        <td><?= h($row->customer->client_branch_code ?? '') ?></td>
                        <td><?= h($row->customer->tel_only ?? $row->customer->tel ?? '') ?></td>
                        <td><?= h($row->customer->client_name ?? '') ?></td>
                        <td><?= h($row->customer->client_branch_name ?? '') ?></td>
                        <td><?= h($row->customer->sales_person ?? '') ?></td>

                        <?php
                        $overviewValue = $row->summary ?? $row->overview ?? '';
                        $addressValue = implode('', array_filter([
                            $row->customer->addr1 ?? '',
                            $row->customer->addr2 ?? '',
                            $row->customer->addr3 ?? '',
                        ], static fn($value) => $value !== '' && $value !== null));
                        $statusKey = (string)$row->status;
                        ?>
                        <td><?= h(mb_strlen((string)$overviewValue) > 20 ? mb_substr((string)$overviewValue, 0, 20) . '...' : (string)$overviewValue) ?></td>
                        <td><?= h($statusOpt[$statusKey] ?? $statusOpt[(int)$statusKey] ?? $row->status ?? '') ?></td>
                        <td><?= h(mb_strlen((string)$addressValue) > 17 ? mb_substr((string)$addressValue, 0, 17) . '...' : $addressValue) ?></td>
                        <td width="8%"><?= h($row->op_incharge) ?></td>
                        <td width="8%"><?= $row->created ? h($row->created->format('Y-m-d H:i')) : '' ?></td>

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
</div>

<style>
    .search-label {
        vertical-align: middle;
        color: #ffffff;
        background-color: #149c82;
    }
</style>
