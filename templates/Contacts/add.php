<?php
$this->assign('title', '対応履歴登録');
$currentUser = $this->request->getAttribute('identity');
?>

<script>
    $(function() {
        $(".datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd'
        });
    });
</script>

<!-- 顧客情報 -->
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">顧客情報</h3>
    </div>

    <table class="table table-bordered">
        <tr>
            <th width="15%">得意先CD</th>
            <td><?= h($customer->client_cd) ?></td>
            <th width="15%">会社・支店名</th>
            <td><?= h($customer->company_name) ?></td>
        </tr>
        <tr>
            <th>氏名</th>
            <td><?= h($customer->name) ?></td>
            <th>番号種別</th>
            <td><?= h($customer->type_of_number) ?></td>
        </tr>
        <tr>
            <th>TEL</th>
            <td><?= h($customer->tel) ?></td>
            <th>FAX</th>
            <td><?= h($customer->fax) ?></td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td><?= h($customer->email) ?></td>
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


<!-- 対応履歴登録フォーム -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">対応履歴登録</h3>
    </div>

    <?= $this->Form->create($contact, ['url' => ['action' => 'add', $customer->id]]) ?>
    <?= $this->Form->control('customer_id', ['type' => 'hidden', 'value' => $customer->id]) ?>

    <div class="panel-body">
        <!-- 概要 -->
        <div class="form-group">
            <label>概要</label>
            <?= $this->Form->control('summary', [
                'label' => false,
                'type' => 'select',
                'options' => [
                    '架電' => '架電',
                    '社内' => '社内',
                    '依頼' => '依頼',
                    '問い合わせ' => '問い合わせ',
                    'クレーム' => 'クレーム',
                    'その他' => 'その他'
                ],
                'empty' => '-----',
                'class' => 'form-control'
            ]) ?>
        </div>

        <!-- ステータス -->
        <div class="form-group">
            <label>ステータス</label>
            <?= $this->Form->control('status', [
                'label' => false,
                'type' => 'select',
                'options' => [
                    '未対応' => '未対応',
                    '対応済' => '対応済'
                ],
                'empty' => '-----',
                'class' => 'form-control'
            ]) ?>
        </div>

        <!-- 対応内容 -->
        <div class="form-group">
            <label>対応内容</label>
            <?= $this->Form->control('remark', [
                'label' => false,
                'type' => 'textarea',
                'rows' => 4,
                'class' => 'form-control'
            ]) ?>
        </div>

        <div style="text-align:center; margin:15px 0;">
            <?= $this->Form->button('キャンセル', [
                'name' => 'back',
                'value' => '1',
                'class' => 'btn-default btn-xs',
                'style' => 'width:150px;height:30px;cursor:pointer; margin-right:10px;'
            ]) ?>
            <?= $this->Form->button('登　録', [
                'type' => 'submit',
                'name' => 'add',
                'class' => 'btn-info btn-xs',
                'style' => 'width:150px;height:30px;cursor:pointer'
            ]) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form");
    form.addEventListener("submit", function(e) {
        const summary = form.querySelector("select[name='summary']").value;
        const status = form.querySelector("select[name='status']").value;

        if (summary === "" || status === "") {
            alert("概要とステータスは必ず選択してください。");
            e.preventDefault(); // stop form submission
        }
    });
});
</script>

</div>

<!-- 対応履歴一覧テーブル -->
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
