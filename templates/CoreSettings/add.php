<div class="panel panel-info filterable">
    <div class="panel-heading">
        <h3 class="panel-title">高度な設定登録</h3>
    </div>

    <?= $this->Form->create($setting, ['url' => ['action' => 'add']]) ?>

    <table class="table table-hover">
        <tbody>

            <tr>
                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">*コンタクトセンターID</td>
                <td><?= $this->Form->control('ccid', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>

                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">ホスト名</td>
                <td><?= $this->Form->control('host_name', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>
            </tr>

            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">DNCデフォルトLeadID</td>
                <td><?= $this->Form->control('dnc_lead_id', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>

                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">DNC API URL</td>
                <td><?= $this->Form->control('dnc_api_url', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>
            </tr>

            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">通録ファイル URL</td>
                <td><?= $this->Form->control('voicelog_url', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>

                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">コール履歴 API URL</td>
                <td><?= $this->Form->control('ch_api_url', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>
            </tr>

            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">手動発信API URL</td>
                <td><?= $this->Form->control('dial_api_url', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>

                <td></td>
                <td></td>
            </tr>

            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">コール履歴表示件数</td>
                <td><?= $this->Form->control('limit', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>

                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">コールバック通知時間（分）</td>
                <td><?= $this->Form->control('callback_notice_time', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>
            </tr>

            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">Ameyoパラメータ取得確認アラート</td>
                <td><?= $this->Form->control('ameyo_alert_enabled', [
                    'label' => false,
                    'div' => false,
                    'type' => 'select',
                    'options' => $activeOpt ?? [],
                    'empty' => '-----',
                    'class' => 'form-control'
                ]) ?></td>
            </tr>

            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">備考</td>
                <td colspan="3"><?= $this->Form->control('memo', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>
            </tr>

        </tbody>
    </table>

    <div align="center" class="btn_margin">
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn-default btn-xs" style="width:150px;height:30px;">キャンセル</a>
        <?= $this->Form->button('登　録', ['class' => 'btn-info btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
