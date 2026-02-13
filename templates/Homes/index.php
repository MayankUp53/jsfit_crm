<?php
/**
 * Homes/index
 * AmeyoJ Dashboard with Manual Dial + Call History + DataTables
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

        <!-- ================= Call History Header ================= -->
        <div class="panel-heading" style="background-color:midnightblue;color:#fff">
            コール履歴一覧（直近<?= h($limit) ?>件まで表示）
        </div>

        <!-- ================= Call History Table ================= -->
        <table id="callsTable" class="table table-hover table-bordered" style="font-size:12px; width:100%;">
    <thead>
        <tr style="vertical-align:middle;color:#ffffff;" bgcolor="#46b8da">
            <th>No.</th>
            <th>キャンペーン</th>
            <th>種別</th>
            <th>キュー名</th>
            <th>発信元</th>
            <th>発信先</th>
            <th>コール結果</th>
            <th>転送先</th>
            <th>ユーザ名</th>
            <th>開始日時</th>
            <th>通話時間</th>
            <th>通録</th>
        </tr>
    </thead>
    <tbody>
        <?php $calls =  $response ?? []; ?>
        <?php if (!empty($calls)): ?>
            <?php foreach ($calls as $row): ?>
                <tr>
                    <td><?= h($row['row_number'] ?? '') ?></td>
                    <td><?= h($row['campaign_name'] ?? '') ?></td>
                    <!-- 種別: already mapped in SQL with CASE ... end as call_type -->
                    <td><?= h($row['call_type'] ?? '') ?></td>
                    <td><?= h($row['agent_queue_name'] ?? '') ?></td>

                    <!-- 発信元: 旧画面と同じく srcphone を表示（ジャンプは phone をキーに） -->
                    <td>
                        <?php if (!empty($row['phone'])): ?>
                            <a href="<?= $this->Url->build([
                                    'controller' => 'Customers',
                                    'action' => 'jump',
                                    $row['phone']
                                ]) ?>">
                                <?= h($row['srcphone'] ?? '') ?>
                            </a>
                        <?php else: ?>
                            <?= h($row['srcphone'] ?? '') ?>
                        <?php endif; ?>
                    </td>

                    <!-- 発信先: 旧画面と同じく dstphone をそのまま表示 -->
                    <td><?= h($row['dstphone'] ?? '') ?></td>

                    <td><?= h($row['disposition_code'] ?? '') ?></td>
                    <td><?= h($row['transfer_to'] ?? '') ?></td>
                    <td><?= h($row['user_name'] ?? '') ?></td>
                    <td><?= h(isset($row['call_originate_time']) ? substr($row['call_originate_time'], 0, 16) : '') ?></td>
                    <td><?= h($row['talk_time'] ?? '') ?></td>
                    <td>
                        <?php if (!empty($row['call_id']) && !empty($row['crt_object_id'])): ?>
                            <a href="javascript:void(0);"
                               onclick="openVoiceLogModal('<?= h($row['call_id']) ?>','<?= h($row['crt_object_id']) ?>');">
                                再生
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="12">コール履歴が見つかりません。</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

   </div>
</div>

<!-- ================= DataTables Initialization ================= -->
<?php $this->start('script'); ?>
<script>
jQuery(document).ready(function($) {
    $('#callsTable').DataTable({
        pageLength: 25,
        lengthChange: false,
        ordering: true,
        responsive: true,
        language: {
            search: "検索:",
            paginate: { previous: "前", next: "次" },
            info: "_START_ - _END_ / _TOTAL_ 件表示",
            infoEmpty: "0 件表示",
            zeroRecords: "該当データがありません"
        },
        // Optional: move search box to left
        dom: '<"row"<"col-sm-6"f><"col-sm-6"l>>tip'
    });
});
</script>
<?php $this->end(); ?>
