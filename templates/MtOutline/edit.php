<div class="panel panel-warning filterable">
    <div class="panel-heading">
        <h3 class="panel-title">概要更新</h3>
    </div>

    <?= $this->Form->create($entry, ['url' => ['action' => 'edit', $entry->id]]) ?>
    <?= $this->Form->control('id', ['type' => 'hidden']) ?>

    <table class="table table-hover">
        <tbody>
            <!-- 概要名称 & 表示順 -->
            <tr>
                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">*概要名称</td>
                <td>
                    <?= $this->Form->control('item_name', [
                        'label' => false, 
                        'div' => false, 
                        'class' => 'form-control'
                    ]) ?>
                    <?= $this->Form->error('item_name') ?>
                </td>
                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">表示順</td>
                <td>
                    <?= $this->Form->control('disp_cd', [
                        'label' => false, 
                        'div' => false, 
                        'class' => 'form-control',
                        'type' => 'number'
                    ]) ?>
                    <?= $this->Form->error('disp_cd') ?>
                </td>
            </tr>

            <!-- 状態 -->
            <tr>
                <td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">状態</td>
                <td colspan="3">
                    <?= $this->Form->control('active', [
                        'label' => false,
                        'div'   => false,
                        'class' => 'form-control',
                        'id'    => 'active',
                        'type'  => 'select',
                        'options' => ['1' => '有効', '0' => '無効'],
                        'empty' => '-- 選択してください --',
                        'required' => true,
                        'value' => $entry->active ?? '' // show current value
                    ]) ?>
                    <?= $this->Form->error('active') ?>
                </td>
            </tr>

            <!-- 登録・更新情報 -->
            <tr>
                <td bgcolor="#ff8c00">登録日時</td>
                <td><?= h($entry->created ?? '-') ?></td>
                <td bgcolor="#ff8c00">登録者</td>
                <td><?= h($opeOpt[$entry->created_user_id] ?? '-') ?></td>
            </tr>
            <tr>
                <td bgcolor="#ff8c00">更新日時</td>
                <td><?= h($entry->updated ?? '-') ?></td>
                <td bgcolor="#ff8c00">更新者</td>
                <td><?= h($opeOpt[$entry->updated_user_id] ?? '-') ?></td>
            </tr>
        </tbody>
    </table>

    <div align="center" class="btn_margin">
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn-default btn-xs" style="width:150px;height:30px;">キャンセル</a>
        <?= $this->Form->button('更　新', [
            'class' => 'btn-warning btn-xs', 
            'style' => 'width:150px;height:30px;cursor:pointer',
            'id' => 'submitBtn'
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("overviewForm") || document.getElementById("statusForm"); // fallback
    const submitBtn = document.getElementById("submitBtn");

    submitBtn.addEventListener("click", function (e) {
        const itemName = document.getElementById("item_name").value.trim();
        const isActive = document.getElementById("active").value;

        if (itemName === "") {
            alert("概要名称は必須です。");
            e.preventDefault();
            return false;
        }

        if (isActive === "") {
            alert("状態を選択してください。");
            e.preventDefault();
            return false;
        }
    });
});
</script>
