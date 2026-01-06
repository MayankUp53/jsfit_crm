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

            <!-- Row 1 -->
            <tr>
                <td width="10%" class="search-label">氏名</td>
                <td width="23%">
                    <?= $this->Form->control('Search.name', [
                        'label' => false,
                        'value' => $search['name'] ?? '',
                        'class' => 'form-control'
                    ]) ?>
                </td>

                <td width="10%" class="search-label">電話番号</td>
                <td width="23%">
                    <?= $this->Form->control('Search.phone', [
                        'label' => false,
                        'value' => $search['phone'] ?? '',
                        'class' => 'form-control'
                    ]) ?>
                </td>

                <td width="10%" class="search-label">メール</td>
                <td width="23%">
                    <?= $this->Form->control('Search.email', [
                        'label' => false,
                        'value' => $search['email'] ?? '',
                        'class' => 'form-control'
                    ]) ?>
                </td>
            </tr>

            <!-- Row 2 -->
            <tr>
                <td class="search-label">FAX</td>
                <td>
                    <?= $this->Form->control('Search.fax', [
                        'label' => false,
                        'value' => $search['fax'] ?? '',
                        'class' => 'form-control'
                    ]) ?>
                </td>

                <td class="search-label">得意先CD</td>
                <td>
                    <?= $this->Form->control('Search.client_cd', [
                        'label' => false,
                        'value' => $search['client_cd'] ?? '',
                        'class' => 'form-control',
                        'type' => 'number'
                    ]) ?>
                </td>

                <td class="search-label">ステータス</td>
                <td>
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

            <!-- Row 3 -->
            <tr>
                <td class="search-label">登録日</td>
                <td>
                    <div class="d-flex align-items-center">
                        <?= $this->Form->control('Search.created_from', [
                            'label' => false,
                            'value' => $search['created_from'] ?? '',
                            'class' => 'form-control datepicker',
                            'placeholder' => 'From'
                        ]) ?>
                        <span style="margin:0 5px;">~</span>
                        <?= $this->Form->control('Search.created_to', [
                            'label' => false,
                            'value' => $search['created_to'] ?? '',
                            'class' => 'form-control datepicker',
                            'placeholder' => 'To'
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
                            'placeholder' => 'From'
                        ]) ?>
                        <span style="margin:0 5px;">~</span>
                        <?= $this->Form->control('Search.updated_to', [
                            'label' => false,
                            'value' => $search['updated_to'] ?? '',
                            'class' => 'form-control datepicker',
                            'placeholder' => 'To'
                        ]) ?>
                    </div>
                </td>

                <td class="search-label">概要</td>
                <td>
                    <?= $this->Form->control('Search.summary', [
                        'label' => false,
                        'type' => 'select',
                        'multiple' => true,
                        'options' => $overviewOpt ?? [],
                        'value' => $search['summary'] ?? [],
                        'class' => 'form-control'
                    ]) ?>
                </td>
            </tr>

            <!-- Row 4 -->
            <tr>
                <td class="search-label">担当OP</td>
                <td>
                    <?= $this->Form->control('Search.op_incharge', [
                        'label' => false,
                        'type' => 'select',
                        'options' => $allUsersOpt ?? [], // ['1'=>'Sato','2'=>'Tanaka']
                        'value' => $search['op_incharge'] ?? [],
                        "multiple" => true,
                        'class' => 'form-control'
                    ]) ?>
                </td>


                <td></td>
                <td></td>
                <td></td>
                <td></td>
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
                    <th width="8%">得意先CD</th>
                    <th width="12%">会社・支店名</th>
                    <th width="10%">氏名</th>
                    <th width="8%">番号種別</th>
                    <th width="10%">TEL</th>
                    <th width="15%">対応内容</th>
                    <th width="8%">ステータス</th>
                    <th width="8%">担当</th>
                    <th width="8%">登録日</th>
                    <th width="8%">更新日</th>
                    <th width="13%">操作</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($contacts as $row): ?>
                    <tr ondblclick="window.location='<?= $this->Url->build([
                                                            'controller' => 'Contacts',
                                                            'action' => 'view',
                                                            $row->id ?? ''
                                                        ]) ?>'">

                        <td width="8%"><?= h($row->customer->client_cd ?? '') ?></td>
                        <td width="12%"><?= h($row->customer->company_name ?? '') ?></td>
                        <td width="10%"><?= h($row->customer->name ?? '') ?></td>
                        <td width="8%"><?= h($row->customer->type_of_number ?? '') ?></td>
                        <td width="10%"><?= h($row->customer->tel ?? '') ?></td>

                        <td width="15%"><?= h($row->summary) ?></td>
                        <td width="8%"><?= h($row->status) ?></td>
                        <td width="8%"><?= h($row->op_incharge) ?></td>
                        <td width="8%"><?= $row->created ? h($row->created->format('Y-m-d H:i')) : '' ?></td>
                        <td width="8%"><?= $row->updated ? h($row->updated->format('Y-m-d H:i')) : '' ?></td>

                        <td width="13%">
                            <a href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>"
                                class="btn btn-xs btn-primary">編集</a>

                            <?php if ($userAuth !== null && $userAuth != 0): ?>
                                <?= $this->Form->postLink('削除', ['action' => 'delete', $row->id], [
                                    'confirm' => __('対応履歴を削除します。よろしいですか？'),
                                    'class' => 'btn btn-xs btn-danger',
                                    'style' => 'margin-left:6px;',
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
</div>

<style>
    .search-label {
        vertical-align: middle;
        color: #ffffff;
        background-color: #149c82;
    }
</style>