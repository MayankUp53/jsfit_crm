<?php
$row = $coreSettings->first() ?: null;
$id = $row?->id;
?>
<div class="panel panel-primary">
    <div style="background-color:#d43f3a" class="panel-heading">
        <h3 class="panel-title">高度な設定</h3>
    </div>
    <br>
    <table class="table table-striped table-hover">
        <tr>
            <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">コンタクトセンターID</td>
            <td><?= h($row->ccid ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">ホスト名</td>
            <td><?= h($row->host_name ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">DNC Default LeadID</td>
            <td><?= h($row->dnc_lead_id ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">DNC API URL</td>
            <td><?= h($row->dnc_api_url ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">通録ファイル URL</td>
            <td><?= h($row->voicelog_url ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">コール履歴 API URL</td>
            <td><?= h($row->ch_api_url ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">コール履歴表示件数</td>
            <td><?= h($row->limit ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">Manual Dial API URL</td>
            <td><?= h($row->dial_api_url ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">コールバック通知時間（分）</td>
            <td><?= h($row->callback_notice_time ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">備考</td>
            <td><?= h($row->memo ?? '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">更新者</td>
            <td><?= h(($opeOpt[$row->updated_user_id] ?? '') ?: '') ?>&nbsp;</td>
        </tr>
        <tr>
            <td style="vertical-align:middle;color:#ffffff;" bgcolor="#20b2aa">更新日時</td>
            <td><?= h(substr((string)($row->updated ?? ''), 0, 16)) ?>&nbsp;</td>
        </tr>
    </table>

    <div style="width:100%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
        <button type="button" style="width:150px;cursor:pointer;" onclick="location.href='<?= $this->Url->build('/settings') ?>';" class="btn-default btn-xs">戻る</button>
        <?php if (!$row): ?>
            <button style="width:150px;cursor:pointer;" onclick="location.href='<?= $this->Url->build(['action' => 'add']) ?>';" class="btn-info btn-xs">追加</button>
        <?php else: ?>
            <button style="width:150px;cursor:pointer;" onclick="location.href='<?= $this->Url->build(['action' => 'edit', $id]) ?>';" class="btn-warning btn-xs">編集</button>
        <?php endif; ?>
    </div>
</div>
