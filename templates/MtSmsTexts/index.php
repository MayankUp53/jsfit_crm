<div class="panel panel-primary filterable">
    <div class="panel-heading">
        <h3 class="panel-title">SMS文面一覧</h3>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>表示順</th>
                <th>キャンペーンID</th>
                <th>名称</th>
                <th>SMS文面</th>
                <th>メモ</th>
                <th>有効無効</th>
                <th>操作</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($mtSmsTexts as $row): ?>
                <tr ondblclick="location.href='<?= $this->Url->build(['action' => 'edit', $row->id]) ?>'">
                    <td><?= h($row->id) ?></td>
                    <td><?= h($row->disp_cd) ?></td>
                    <td><?= h($row->campaign_id) ?></td>
                    <td><?= h($row->item_name) ?></td>
                    <td><?= h(mb_strimwidth((string)$row->sms_text, 0, 120, '...')) ?></td>
                    <td><?= h($row->item_memo) ?></td>
                    <td><?= h($activeOpt[$row->active_flg] ?? '') ?></td>
                    <td>
                        <a class="btn btn-xs btn-success"
                           href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>">
                            編集
                        </a>

                        <?= $this->Form->postLink(
                            '削除',
                            ['action' => 'delete', $row->id],
                            [
                                'confirm' => 'SMS文面を削除しますか？',
                                'class' => 'btn btn-xs btn-danger'
                            ]
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="width:50%;" align="center" class="btn_margin">
        <button type="button"
                class="btn-info btn-xs"
                style="width:150px;height:30px;cursor:pointer"
                onclick="location.href='<?= $this->Url->build(['action' => 'add']) ?>'">
            新規登録
        </button>

        <button type="button"
                class="btn-default btn-xs"
                style="width:150px;height:30px;cursor:pointer;margin-left:10px;"
                onclick="location.href='<?= $this->Url->build('/settings') ?>';">
            戻る
        </button>
    </div>
</div>
