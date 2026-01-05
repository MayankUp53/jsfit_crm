<div class="panel panel-warning filterable">
    <div class="panel-heading">
        <h3 class="panel-title">ステータス更新</h3>
    </div>

    <?= $this->Form->create($entry, ['url' => ['action' => 'edit', $entry->id]]) ?>
    <?= $this->Form->control('id', ['type' => 'hidden']) ?>

    <table class="table table-hover">
        <tbody>
            <!-- 名称 -->
            <tr>
                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">*名称</td>
                <td>
                    <?= $this->Form->control('item_name', [
                        'label' => false,
                        'div'   => false,
                        'class' => 'form-control',
                        'required' => true
                    ]) ?>
                    <?= $this->Form->error('item_name') ?>
                </td>

                <!-- 表示順 -->
                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">表示順</td>
                <td>
                    <?= $this->Form->control('disp_cd', [
                        'label' => false,
                        'div'   => false,
                        'class' => 'form-control',
                        'type'  => 'number'
                    ]) ?>
                    <?= $this->Form->error('disp_cd') ?>
                </td>
            </tr>

            <!-- 状態 -->
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">状態</td>
                <td colspan="3">
                    <?= $this->Form->control('active', [
                        'label' => false,
                        'div'   => false,
                        'class' => 'form-control',
                        'type'  => 'select',
                        'options' => ['1' => '有効', '0' => '無効'],
                        'empty' => '-- 選択してください --',
                        'value' => $entry->active 
                    ]) ?>
                    <?= $this->Form->error('active') ?>
                </td>
            </tr>

            <!-- 登録・更新情報 -->
            <tr>
                <td bgcolor="#ff8c00">登録日時</td>
                <td><?= h($entry->created) ?></td>
                <td bgcolor="#ff8c00">登録者</td>
                <td><?= h($opeOpt[$entry->created_user_id] ?? '-') ?></td>
            </tr>
            <tr>
                <td bgcolor="#ff8c00">更新日時</td>
                <td><?= h($entry->updated) ?></td>
                <td bgcolor="#ff8c00">更新者</td>
                <td><?= h($opeOpt[$entry->updated_user_id] ?? '-') ?></td>
            </tr>
        </tbody>
    </table>

    <div align="center" class="btn_margin">
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn-default btn-xs" style="width:150px;height:30px;">キャンセル</a>
        <?= $this->Form->button('更　新', [
            'class' => 'btn-warning btn-xs', 
            'style' => 'width:150px;height:30px;cursor:pointer'
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
