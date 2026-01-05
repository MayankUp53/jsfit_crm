<script>
function formSubmit() {
    var pattern = document.querySelector('.pattern')?.value;
    if (!pattern) {
        alert('取込パターンを選択してください');
        return false;
    }
    return confirm('取込を開始します。よろしいですか？');
}
</script>

<?php $patternOpt = ['1' => '取込パターン①（新規）']; ?>

<div class="bs-docs-section clearfix">
    <div class="panel panel-primary filterable">
        <div class="panel-heading">
            <h3 class="panel-title">データ取込</h3>
        </div>
        <?= $this->Form->create(null, [
            'url' => ['controller' => 'Imports', 'action' => 'import'],
            'type' => 'file',
            'enctype' => 'multipart/form-data',
        ]) ?>
        <table class="table table-hover">
            <tbody>
                <tr>
                    <th width="10%" style="vertical-align:middle">&nbsp;顧客データ</th>
                    <td width="20%" style="vertical-align:middle">
                        <?= $this->Form->control('pattern', [
                            'label' => false,
                            'div' => false,
                            'type' => 'select',
                            'class' => 'form-control if100 pattern',
                            'options' => $patternOpt,
                            'empty' => '-----',
                        ]) ?>
                    </td>
                    <td width="40%" style="vertical-align:middle">
                        <?= $this->Form->file('result') ?>
                    </td>
                    <td width="30%" style="vertical-align:middle">
                        <button type="button" style="width:100px;height:34px;cursor:pointer;" onclick="location.href='<?= $this->Url->build(['controller' => 'Imports', 'action' => 'template']) ?>';" class="btn-warning btn-xs">雛形DL</button>
                        <?= $this->Form->button('取　込', [
                            'div' => false,
                            'id' => 'import',
                            'name' => 'import',
                            'type' => 'submit',
                            'class' => 'btn-info btn-xs',
                            'style' => 'width:100px;height:34px;cursor:pointer',
                            'onclick' => 'return formSubmit();',
                        ]) ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?= $this->Form->end() ?>
    </div>

    <div class="panel panel-primary filterable">
        <div class="panel-heading">
            <h3 class="panel-title">設定項目一覧</h3>
        </div>
        <table class="table table-hover">
          <tbody>
     <tr>
        <th style="vertical-align:middle">&nbsp;ステータスリスト</th>
        <td width="50%"><button onclick="location.href='<?= $this->Url->build('/mt-statuses') ?>';" class="btn-info btn-xs" style="width:150px;height:30px;cursor:pointer">一覧</button></td>
    </tr>
   <!-- <tr>
        <th style="vertical-align:middle">&nbsp;店舗への連絡</th>
        <td width="50%"><button onclick="location.href='<?= $this->Url->build('/mt-queues') ?>';" class="btn-info btn-xs" style="width:150px;height:30px;cursor:pointer">一覧</button></td>
    </tr>
    <tr>
        <th style="vertical-align:middle">&nbsp;商品案内</th>
        <td width="50%"><button onclick="location.href='<?= $this->Url->build('/mt-products') ?>';" class="btn-info btn-xs" style="width:150px;height:30px;cursor:pointer">一覧</button></td>
    </tr> -->
    <tr>
        <th style="vertical-align:middle">&nbsp;電話帳＆店舗リスト</th>
        <td width="50%"><button onclick="location.href='<?= $this->Url->build('/mt-phonelists') ?>';" class="btn-info btn-xs" style="width:150px;height:30px;cursor:pointer">一覧</button></td>
    </tr>
    <tr>
        <th style="vertical-align:middle">&nbsp;発信者番号リスト</th>
        <td width="50%"><button onclick="location.href='<?= $this->Url->build('/mt-cids') ?>';" class="btn-info btn-xs" style="width:150px;height:30px;cursor:pointer">一覧</button></td>
    </tr>
    <tr>
        <th style="vertical-align:middle">&nbsp;概要リスト</th>
        <td width="50%"><button onclick="location.href='<?= $this->Url->build('/mt-overview') ?>';" class="btn-info btn-xs" style="width:150px;height:30px;cursor:pointer">一覧</button></td>
    </tr>
    <tr>
        <th style="vertical-align:middle">&nbsp;高度な設定</th>
        <td width="50%"><button onclick="location.href='<?= $this->Url->build('/core-settings') ?>';" class="btn-danger btn-xs" style="width:150px;height:30px;cursor:pointer">一覧</button></td>
    </tr>
</tbody>

        </table>
    </div>
</div>
