<?php $this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken')); ?>

<?php $this->start('css'); ?>
<style>
    #phone-list {
        list-style-type: none;
        margin: 0;
        padding: 0;
        width: 60%;
    }
    .center-btn {
    display: flex;
    justify-content: center;
    gap: 10px;
}


    #phone-list li {
        margin: 3px 0;
        padding: 0.6em 0.8em;
        font-size: 15px;
        font-weight: bold;
        border-radius: 4px;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    #phone-list .meta {
        font-weight: normal;
        margin-left: 10px;
    }

    .btn-group {
        margin-top: 20px;
    }

    .btn-group button {
        margin-right: 10px;
        height: 30px;
        width: 150px;
        cursor: pointer;
    }
</style>
<?php $this->end(); ?>

<div class="panel panel-primary filterable">
    <div class="panel-heading">
        <h3 class="panel-title">電話帳</h3>
    </div>

    <br />

    <ul id="phone-list">
        <?php foreach ($mtPhonelists as $row): ?>
            <li>
                <span class="disp-cd"><?= h($row->disp_cd) ?></span>
                <?= h($row->item_name) ?>
                <span class="meta">
                    <?= h($row->phone) ?>
                    (<?= h($activeOpt[$row->active_flg] ?? '') ?>)
                </span>

                <span class="actions">
                    <a class="btn btn-xs btn-success"
                       href="<?= $this->Url->build(['action' => 'edit', $row->id]) ?>">
                        編集
                    </a>

                    <?= $this->Form->postLink(
                        '削除',
                        ['action' => 'delete', $row->id],
                        [
                            'confirm' => '削除しますか？',
                            'class' => 'btn btn-xs btn-danger'
                        ]
                    ) ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="btn-group center-btn" style="margin-bottom:20px;">
    <button type="button" class="btn-default btn-xs"
            onclick="location.href='<?= $this->Url->build(['action' => 'add']) ?>'">
        新規登録
    </button>

    <button type="button" class="btn-default btn-xs"
            onclick="history.back();">
        戻る
    </button>
</div>

</div>
