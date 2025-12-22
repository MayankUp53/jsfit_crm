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
    <table class="table">
        <tbody>
            <tr>
                <td width="10%" class="search-label">氏名</td>
                <td width="23%"><?= $this->Form->control('Search.name', ['label' => false, 'value' => $search['name'] ?? '', 'class' => 'form-control']) ?></td>
                <td width="10%" class="search-label">電話番号</td>
                <td width="23%"><?= $this->Form->control('Search.phone', ['label' => false, 'value' => $search['phone'] ?? '', 'class' => 'form-control']) ?></td>
                <td width="10%" class="search-label">メール</td>
                <td width="23%"><?= $this->Form->control('Search.email', ['label' => false, 'value' => $search['email'] ?? '', 'class' => 'form-control']) ?></td>
            </tr>
            <tr>
                <td class="search-label">表示No</td>
                <td><?= $this->Form->control('Search.disp_no', ['label' => false, 'value' => $search['disp_no'] ?? '', 'class' => 'form-control']) ?></td>
                <td class="search-label">顧客ID</td>
                <td><?= $this->Form->control('Search.id', ['label' => false, 'value' => $search['id'] ?? '', 'class' => 'form-control', 'type' => 'number']) ?></td>
                <td class="search-label">登録日</td>
                <td>
                    <div class="row">
                        <div class="col-xs-6">
                            <?= $this->Form->control('Search.created_from', ['label' => false, 'value' => $search['created_from'] ?? '', 'class' => 'form-control datepicker', 'placeholder' => 'From']) ?>
                        </div>
                        <div class="col-xs-6">
                            <?= $this->Form->control('Search.created_to', ['label' => false, 'value' => $search['created_to'] ?? '', 'class' => 'form-control datepicker', 'placeholder' => 'To']) ?>
                        </div>
                    </div>
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
                    <td width="7%">顧客ID</td>
                    <td width="12%">表示No</td>
                    <td width="18%">氏名</td>
                    <td width="15%">電話番号</td>
                    <td width="18%">メール</td>
                    <td width="15%">登録日</td>
                    <td width="15%">更新日</td>
                    <td width="8%">操作</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr onDblClick="location.href='<?= $this->Url->build(['controller' => 'Contacts', 'action' => 'add', $customer->id]) ?>'">
                        <td><?= h($customer->id) ?></td>
                        <td><?= h($customer->disp_no) ?></td>
                        <td><?= h($customer->name) ?></td>
                        <td><?= h($customer->phone) ?></td>
                        <td><?= h($customer->email) ?></td>
                        <td><?= $customer->created ? h($customer->created->format('Y-m-d H:i')) : '' ?></td>
                        <td><?= $customer->updated ? h($customer->updated->format('Y-m-d H:i')) : '' ?></td>
                        <td>
                            <a href="<?= $this->Url->build(['action' => 'edit', $customer->id]) ?>" class="btn btn-xs btn-primary">編集</a>
                            <?php if ($currentUser && $currentUser->getOriginalData()->auth != "0"): ?>
                                <?= $this->Form->postLink('削除', ['action' => 'delete', $customer->id], [
                                    'confirm' => __('顧客情報を削除します。よろしいですか？'),
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
</style>
