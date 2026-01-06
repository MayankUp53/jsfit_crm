<?php
/**
 * Homes/index
 * Simplified port of the CakePHP 2.x dashboard with manual dial + call history.
 * DataTables/JS can be re-added later if needed; keeping markup readable for now.
 */
?>

<div class="bs-docs-section clearfix">
    <div class="panel panel-primary filterable">
        <div class="panel-heading">
            <h3 class="panel-title">AmeyoJ ダッシュボード</h3>
        </div>

        <?php if (!empty($cidOpt)): ?>
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Homes', 'action' => 'manualDialApi'],
                'class' => 'form-inline',
            ]) ?>
            <table class="table">
                <tr>
                    <td width="20%">
                        <?= $this->Form->control('cid', [ 
                            'label' => false,
                            'type' => 'select',
                            'options' => $cidOpt,
                            'empty' => '--- 通知番号 ---',
                            'class' => 'form-control',
                        ]) ?>
                    </td>
                    <td width="20%">
                        <?= $this->Form->control('tel', [
                            'label' => false,
                            'type' => 'text',
                            'placeholder' => '発信先TEL',
                            'class' => 'form-control',
                        ]) ?>
                    </td>
                    <td width="6%">
                        <button type="button" class="btn btn-success btn-xs" style="height:34px;width:80px"
                            onclick="window.open('<?= $this->Url->build(['action' => 'phoneList']) ?>','','width=500,height=700');">
                            電話帳
                        </button>
                    </td>
                    <td>
                        <?= $this->Form->submit('発信', [
                            'class' => 'btn btn-info btn-xs',
                            'style' => 'height:34px;width:80px;cursor:pointer;',
                        ]) ?>
                    </td>
                </tr>
            </table>
            <?= $this->Form->end() ?>
        <?php endif; ?>

        <div class="panel-heading" style="background-color:midnightblue;color:#fff">
            コール履歴一覧（直近<?= h($limit) ?>件まで表示）
        </div>

        <table class="table table-hover" style="font-size:12px">
            <thead>
                <tr style="vertical-align:middle;color:#ffffff;" bgcolor="#46b8da">
                    <th>No.</th>
                    <th>キャンペーン</th>
                    <th>結果</th>
                    <th>電話番号</th>
                    <th>対応者</th>
                    <th>発信日時</th>
                    <th>通話時間(秒)</th>
                    <th>録音</th>
                </tr>
            </thead>
            <tbody>
                <?php $calls = $pagedCalls ?? $response ?? []; ?>
                <?php if (!empty($calls)): ?>
                    <?php foreach ($calls as $row): ?>
                        <tr>
                            <td><?= h($row['row_number'] ?? '') ?></td>
                            <td><?= h($row['campaign_name'] ?? '') ?></td>
                            <td><?= h($row['result'] ?? '') ?></td>
                            <td><?= h($row['phone'] ?? '') ?></td>
                            <td><?= h($row['user_name'] ?? '') ?></td>
                            <td><?= h($row['call_originate_time'] ?? '') ?></td>
                            <td><?= h($row['talk_time'] ?? '') ?></td>
                            <td>
                                <?php if (!empty($row['call_id']) && !empty($row['crt_object_id'])): ?>
                                    <a href="javascript:void(0);" onclick="window.open('<?= $this->Url->build(['action' => 'play', $row['call_id'], $row['crt_object_id']]) ?>','','width=310,height=80');">
                                        再生
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">コール履歴が見つかりません。</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
   </div>

    <?php if (!empty($pageCount) && $pageCount > 1): ?>
    <div style="width:100%" align="center" class="paginateLinks btn_margin">
        <ul class="pagination pagination-sm">
            <?php for ($p = 1; $p <= $pageCount; $p++): ?>
                <?php if ($p === ($page ?? 1)): ?>
                    <li class="active"><span><?= $p ?></span></li>
                <?php else: ?>
                    <li><a href="<?= $this->Url->build(['?' => ['page' => $p]]) ?>"><?= $p ?></a></li>
                <?php endif; ?>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">リマインダー（コールバック）</h3>
        </div>
        <table class="table table-striped table-condensed">
            <thead>
                <tr>
                    <th>予定日時</th>
                    <th>電話番号</th>
                    <th>備考</th>
                    <th>登録者</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($callbacks as $callback): ?>
                    <tr>
                        <td><?= h($callback->callback_datetime) ?></td>
                        <td><?= h($callback->phone) ?></td>
                        <td><?= h($callback->remark) ?></td>
                        <td><?= h($opeOpt[$callback->created_user_id] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div> -->
</div>
