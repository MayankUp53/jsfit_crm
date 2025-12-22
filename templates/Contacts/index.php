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
            <tr>
                <td class="search-label" width="10%">顧客氏名</td>
                <td width="23%"><?= $this->Form->control('Search.customer_name', ['label' => false, 'value' => $search['customer_name'] ?? '', 'class' => 'form-control']) ?></td>
                <td class="search-label" width="10%">顧客電話</td>
                <td width="23%"><?= $this->Form->control('Search.customer_phone', ['label' => false, 'value' => $search['customer_phone'] ?? '', 'class' => 'form-control']) ?></td>
                <td class="search-label" width="10%">顧客メール</td>
                <td width="23%"><?= $this->Form->control('Search.customer_email', ['label' => false, 'value' => $search['customer_email'] ?? '', 'class' => 'form-control']) ?></td>
            </tr>
            <tr>
                <td class="search-label">顧客ID</td>
                <td><?= $this->Form->control('Search.customer_id', ['label' => false, 'value' => $search['customer_id'] ?? '', 'class' => 'form-control', 'type' => 'number']) ?></td>
                <td class="search-label">履歴ID</td>
                <td><?= $this->Form->control('Search.contact_id', ['label' => false, 'value' => $search['contact_id'] ?? '', 'class' => 'form-control', 'type' => 'number']) ?></td>
                <td class="search-label">架電番号</td>
                <td><?= $this->Form->control('Search.phone', ['label' => false, 'value' => $search['phone'] ?? '', 'class' => 'form-control']) ?></td>
            </tr>
            <tr>
                <td class="search-label">ステータス</td>
                <td><?= $this->Form->control('Search.status', ['label' => false, 'type' => 'select', 'options' => $statusOpt ?? [], 'empty' => '-----', 'value' => $search['status'] ?? '']) ?></td>
                <td class="search-label">キュー</td>
                <td><?= $this->Form->control('Search.queue_name', ['label' => false, 'type' => 'select', 'options' => $queueOpt ?? [], 'empty' => '-----', 'value' => $search['queue_name'] ?? '']) ?></td>
                <td class="search-label">再コール状態</td>
                <td><?= $this->Form->control('Search.is_done', ['label' => false, 'type' => 'select', 'options' => $callbackStatusOpt ?? [], 'empty' => '-----', 'value' => $search['is_done'] ?? '']) ?></td>
            </tr>
            <tr>
                <td class="search-label">登録者</td>
                <td><?= $this->Form->control('Search.created_user_id', ['label' => false, 'type' => 'select', 'options' => $allUsersOpt ?? [], 'empty' => '-----', 'value' => $search['created_user_id'] ?? '']) ?></td>
                <td class="search-label">登録日(From)</td>
                <td><?= $this->Form->control('Search.created_from', ['label' => false, 'class' => 'form-control datepicker', 'value' => $search['created_from'] ?? '']) ?></td>
                <td class="search-label">登録日(To)</td>
                <td><?= $this->Form->control('Search.created_to', ['label' => false, 'class' => 'form-control datepicker', 'value' => $search['created_to'] ?? '']) ?></td>
            </tr>
            <tr>
                <td class="search-label">再コール日(From)</td>
                <td><?= $this->Form->control('Search.callback_from', ['label' => false, 'class' => 'form-control datepicker', 'value' => $search['callback_from'] ?? '']) ?></td>
                <td class="search-label">再コール日(To)</td>
                <td><?= $this->Form->control('Search.callback_to', ['label' => false, 'class' => 'form-control datepicker', 'value' => $search['callback_to'] ?? '']) ?></td>
                <td class="search-label">概要/備考</td>
                <td>
                    <?= $this->Form->control('Search.summary', ['label' => false, 'class' => 'form-control', 'placeholder' => '概要キーワード', 'value' => $search['summary'] ?? '']) ?>
                    <?= $this->Form->control('Search.remark', ['label' => false, 'class' => 'form-control', 'placeholder' => '備考キーワード', 'style' => 'margin-top:5px;', 'value' => $search['remark'] ?? '']) ?>
                </td>
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
        <table class="table table-striped table-hover" style="font-size:12px">
            <thead>
                <tr height="35" bgcolor="#20b2aa" style="vertical-align:middle;color:#ffffff;">
                    <td width="6%">履歴ID</td>
                    <td width="15%">顧客氏名</td>
                    <td width="12%">顧客電話</td>
                    <td width="12%">架電番号</td>
                    <td width="12%">ステータス</td>
                    <td width="19%">概要</td>
                    <td width="12%">登録日</td>
                    <td width="12%">操作</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $row): ?>
                    <tr ondblclick="location.href='<?= $this->Url->build(['action' => 'view', $row->id]) ?>'">
                        <td><?= h($row->id) ?></td>
                        <td><?= h($row->customer->name ?? '') ?></td>
                        <td><?= h($row->customer->phone ?? '') ?></td>
                        <td><?= h($row->phone) ?></td>
                        <td><?= h($statusOpt[$row->status] ?? $row->status) ?></td>
                        <td><?= h(mb_strimwidth((string)$row->summary, 0, 50, '...')) ?></td>
                        <td><?= $row->created ? h($row->created->format('Y-m-d H:i')) : '' ?></td>
                        <td>
                            <a href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>" class="btn btn-xs btn-primary">編集</a>
                            <?php if ($userAuth !== null && $userAuth != 0): ?>
                                <?= $this->Form->postLink('削除', ['action' => 'delete', $row->id], [
                                    'confirm' => __('対応履歴を削除します。よろしいですか？'),
                                    'class' => 'btn btn-xs btn-danger',
                                    'style' => 'margin-left:3px;',
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
