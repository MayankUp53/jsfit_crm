<?php
$this->assign('title', '対応履歴詳細');
$currentUser = $this->request->getAttribute('identity');
?>

<!-- ================= Customer Details ================= -->
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

    <div class="text-right" style="margin-bottom:10px;">
        <a href="<?= $this->Url->build(['controller' => 'Customers', 'action' => 'edit', $customer->id]) ?>"
           class="btn btn-warning btn-xs">顧客編集</a>
      
 <?php if ($currentUser && $currentUser->getOriginalData()->auth != "0"): ?>
            <?= $this->Form->postLink(
                '顧客削除',
                ['controller' => 'Customers', 'action' => 'delete', $customer->id],
                ['confirm' => '顧客情報を削除します。よろしいですか？', 'class' => 'btn btn-danger btn-xs']
            ) ?>
        <?php endif; ?>
    </div>
</div>

<!-- ================= Main Contact Detail ================= -->
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴詳細</h3>
    </div>

    <table class="table table-bordered">
        <tr>
            <th width="20%">概要</th>
            <td><?= h($contact->summary) ?></td>
        </tr>
        <tr>
            <th>ステータス</th>
            <td><?= h($contact->status) ?></td>
        </tr>
        <tr>
            <th>対応内容</th>
            <td><?= nl2br(h($contact->remark)) ?></td>
        </tr>
       
    </table>

<div class="btn_margin text-center" style="margin-top:10px;">
    <a class="btn btn-xs btn-primary" href="<?= $this->Url->build(['action' => 'edit', $contact->id]) ?>">編集</a>
   <a class="btn btn-xs btn-default" href="<?= $this->Url->build(['action' => 'index',]) ?>">一覧へ</a>

</div>

</div>

<!-- ================= Customer Contact List ================= -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴一覧</h3>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover" style="font-size:12px">
            <thead>
                <tr>
                    <th>得意先CD</th>
                    <th>会社・支店名</th>
                    <th>氏名</th>
                    <th>番号種別</th>
                    <th>TEL</th>
                    <th>概要</th>
                    <th>ステータス</th>
                    <th>担当OP</th>
                    <th>登録日</th>
                    <th width="10%">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $row): ?>
                    <tr>
                        <td><?= h($row->customer->client_cd ?? '') ?></td>
                        <td><?= h($row->customer->company_name ?? '') ?></td>
                        <td><?= h($row->customer->name ?? '') ?></td>
                        <td><?= h($row->customer->type_of_number ?? '') ?></td>
                        <td><?= h($row->customer->tel ?? '') ?></td>
                        <td><?= h($row->summary) ?></td>
                        <td><?= h($row->status) ?></td>
                        <td><?= h($row->op_incharge) ?></td>
                        <td><?= h($row->created ? $row->created->format('Y-m-d H:i') : '') ?></td>
                        <td>
                            <a href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>" class="btn btn-xs btn-primary">編集</a>
                            <?= $this->Form->postLink('削除', ['action' => 'delete', $row->id], [
                                'confirm' => '削除しますか？', 
                                'class' => 'btn btn-xs btn-danger'
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
