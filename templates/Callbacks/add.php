<div class="panel panel-info filterable">
    <div class="panel-heading">
        <h3 class="panel-title">再コール登録</h3>
    </div>
    <?= $this->Form->create($callback, ['url' => ['action' => 'add']]) ?>
    <table class="table table-hover">
        <tbody>
            <tr>
                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">顧客</td>
                <td><?= $this->Form->control('customer_id', ['label' => false, 'div' => false, 'type' => 'select', 'options' => $customers ?? [], 'class' => 'form-control', 'empty' => '-----']) ?></td>
                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">電話番号</td>
                <td><?= $this->Form->control('phone', ['label' => false, 'div' => false, 'class' => 'form-control']) ?></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">再コール日時</td>
                <td><?= $this->Form->control('callback_datetime', ['label' => false, 'div' => false, 'class' => 'form-control datetimepicker']) ?></td>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">ステータス</td>
                <td><?= $this->Form->control('is_done', ['label' => false, 'div' => false, 'type' => 'select', 'options' => $callbackStatusOpt ?? [], 'empty' => '-----', 'class' => 'form-control']) ?></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">備考</td>
                <td colspan="3"><?= $this->Form->control('remark', ['label' => false, 'div' => false, 'type' => 'textarea', 'rows' => 3, 'class' => 'form-control']) ?></td>
            </tr>
        </tbody>
    </table>
    <div align="center" class="btn_margin">
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn-default btn-xs" style="width:150px;height:30px;">キャンセル</a>
        <?= $this->Form->button('登　録', ['class' => 'btn-info btn-xs', 'style' => 'width:150px;height:30px;cursor:pointer']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
