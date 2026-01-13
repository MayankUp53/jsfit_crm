<?php
$this->assign('title', '顧客詳細情報新規登録 ');
?>

<script>
    $(function () {
        $(".datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd'
        });
    });
</script>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">顧客詳細情報新規登録</h3>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">基本</h3>
        </div>

        <div style="margin:15px 0;"></div>
        <?= $this->Form->create($customer, ['id' => 'customerForm']) ?>

        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td width="50%">
                        <label>会社・支店名</label>
                        <?= $this->Form->control('company_name', [
                            'label' => false,
                            'class' => 'form-control'
                        ]) ?>
                    </td>

                    <td width="50%">
                        <label>得意先CD</label>
                        <?= $this->Form->control('client_cd', [
                            'label' => false,
                            'class' => 'form-control'                            
                        ]) ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label>氏名</label>
                        <?= $this->Form->control('name', [
                            'label' => false,
                            'class' => 'form-control'
                        ]) ?>
                    </td>

                    <td>
                        <label>番号種別</label>
                        <?= $this->Form->control('type_of_number', [
                            'label' => false,
                            'type' => 'select',
                            'class' => 'form-control',
                            'empty' => '-----',
                            'options' => [
                                '営業所'   => '営業所',
                                '私用携帯' => '私用携帯',
                                '固定電話' => '固定電話',
                            ]
                        ]) ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label>TEL</label>
                        <?= $this->Form->control('tel', [
                            'label' => false,
                            'class' => 'form-control'
                        ]) ?>
                    </td>

                    <td>
                        <label>FAX</label>
                        <?= $this->Form->control('fax', [
                            'label' => false,
                            'class' => 'form-control'
                        ]) ?>
                    </td>
                </tr>

                <tr>
                    <td >
                        <label>メールアドレス</label>
                        <?= $this->Form->control('email', [
                            'label' => false,
                            'class' => 'form-control',
                            'type' => 'email'
                        ]) ?>
                    </td>
                     <td >
                        <label>メモ</label>
                        <?= $this->Form->control('memo', [
                            'label' => false,
                            'class' => 'form-control'
                        ]) ?>
                    </td>
                        ]) ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
            <!-- Cancel / Back button -->
            <?= $this->Form->button('キャンセル', [
                'type' => 'button',   // <- Important: prevents validation
                'id' => 'cancelBtn',
                'div' => false,
                'style' => 'width:150px;height:30px;cursor:pointer',
                'class' => 'btn-default btn-xs'
            ]) ?>

            <!-- Add / Update button -->
            <?= $this->Form->button('登　録', [
                'type' => 'submit',
                'name' => 'add',       // Controller will check this
                'value' => '1',
                'div' => false,
                'style' => 'width:150px;height:30px;cursor:pointer',
                'class' => 'btn-info btn-xs'
            ]) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<!-- JS to handle Cancel button -->
<script>
document.getElementById('cancelBtn').addEventListener('click', function() {
    window.location.href = '<?= $this->Url->build(['action' => 'index']) ?>';
});
</script>
