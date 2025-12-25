<div class="panel panel-info filterable">
    <div class="panel-heading">
        <h3 class="panel-title">概要登録</h3>
    </div>

    <?= $this->Form->create($entry, ['url' => ['action' => 'add'], 'id' => 'overviewForm']) ?>

    <table class="table table-hover">
        <tbody>
            <tr>
                <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">
                    表示順
                </td>
                <td>
                    <?= $this->Form->control('disp_cd', [
                        'label' => false,
                        'div'   => false,
                        'class' => 'form-control',
                        'id'    => 'disp_cd'
                    ]) ?>
                </td>
            </tr>
            <tr>
                <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">
                    *概要名称
                </td>
                <td>
                    <?= $this->Form->control('item_name', [
                        'label' => false,
                        'div'   => false,
                        'class' => 'form-control',
                        'id'    => 'item_name',
                        'required' => true
                    ]) ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div align="center" class="btn_margin">
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn-default btn-xs" style="width:150px;height:30px;">
            キャンセル
        </a>

        <?= $this->Form->button('登　録', [
            'class' => 'btn-info btn-xs',
            'style' => 'width:150px;height:30px;cursor:pointer',
            'id' => 'submitBtn'
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("overviewForm");
    const submitBtn = document.getElementById("submitBtn");

    submitBtn.addEventListener("click", function (e) {
        const itemName = document.getElementById("item_name").value.trim();
        if (itemName === "") {
            alert("概要名称は必須です。");
            e.preventDefault(); // stop submit
        }
    });
});
</script>
