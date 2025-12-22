<div class="panel panel-primary filterable">
    <div class="panel-heading">
        <h3 class="panel-title">再コール一覧</h3>
    </div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>顧客ID</th>
                <th>電話番号</th>
                <th>予約日時</th>
                <th>備考</th>
                <th>ステータス</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($callbacks as $row): ?>
                <tr>
                    <td><?= h($row->id) ?></td>
                    <td><?= h($row->customer_id) ?></td>
                    <td><?= h($row->phone) ?></td>
                    <td><?= h($row->callback_datetime) ?></td>
                    <td><?= h($row->remark) ?></td>
                    <td><?= h($callbackStatusOpt[$row->is_done] ?? '') ?></td>
                    <td>
                        <a class="btn btn-xs btn-success" href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>">編集</a>
                        <?= $this->Form->postLink('削除', ['action' => 'delete', $row->id], ['confirm' => '削除しますか？', 'class' => 'btn btn-xs btn-danger']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div align="center" class="btn_margin">
        <a href="<?= $this->Url->build(['action' => 'add']) ?>" class="btn-info btn-xs" style="width:150px;height:30px;">新規登録</a>
    </div>
</div>
