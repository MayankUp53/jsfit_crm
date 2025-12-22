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
        <td width="10%" class="search-label">氏名</td>
        <td width="23%">
            <?= $this->Form->control('Search.name', [
                    'label' => false,
                    'value' => $search['name'] ?? '',
                    'class' => 'form-control'
                ]) 
            ?>
        </td>

        <td width="10%" class="search-label">電話番号</td>
        <td width="23%">
            <?= $this->Form->control('Search.phone', [
                    'label' => false,
                    'value' => $search['phone'] ?? '',
                    'class' => 'form-control'
                ]) 
            ?>
        </td>

        <td width="10%" class="search-label">メール</td>
        <td width="23%">
            <?= $this->Form->control('Search.email', [
                    'label' => false,
                    'value' => $search['email'] ?? '',
                    'class' => 'form-control'
                ]) 
            ?>
        </td>
    </tr>

    <tr>
        <td class="search-label">FAX</td>
        <td>
            <?= $this->Form->control('Search.fax', [
                    'label' => false,
                    'value' => $search['fax'] ?? '',
                    'class' => 'form-control'
                ]) 
            ?>
        </td>

        <td class="search-label">得意先CD</td>
        <td>
            <?= $this->Form->control('Search.client_cd', [
                    'label' => false,
                    'value' => $search['client_cd'] ?? '',
                    'class' => 'form-control',
                    'type' => 'number'
                ]) 
            ?>
        </td>

        <td class="search-label">登録日</td>
        <td>
            <div class="d-flex align-items-center">
                <?= $this->Form->control('Search.created_from', [
                        'label' => false,
                        'value' => $search['created_from'] ?? '',
                        'class' => 'form-control datepicker',
                        'placeholder' => 'From'
                    ]) 
                ?>
                <span style="margin: 0 5px;">~</span>
                <?= $this->Form->control('Search.created_to', [
                        'label' => false,
                        'value' => $search['created_to'] ?? '',
                        'class' => 'form-control datepicker',
                        'placeholder' => 'To'
                    ]) 
                ?>
            </div>
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
    <td width="8%">得意先CD</td>
    <td width="15%">会社・支店名</td>
    <td width="12%">氏名</td>
    <td width="10%">番号種別</td>
    <td width="10%">TEL</td>
    <td width="15%">概要</td>
    <td width="10%">ステータス</td>
    <td width="10%">担当OP</td>
    <td width="10%">登録日</td>
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
