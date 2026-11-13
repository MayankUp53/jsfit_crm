<?php
$isEdit = $isEdit ?? false;
$heading = $isEdit ? '顧客詳細情報 更新' : '顧客詳細情報新規登録';
$currentUser = $this->request->getAttribute('identity');
$userAuth = $currentUser ? $currentUser->getOriginalData()->auth : null;
$rows = [
    [['client_code', '得意先CD＊', 6], ['client_branch_code', '得意先支店CD', 3]],
    [['client_name', '得意先名', 30], ['client_branch_name', '得意先支店名', 30]],
    [['addr1', '住所1', 30], ['addr2', '住所2', 30]],
    [['addr3', '住所３', 30], ['tel', 'TEL＊', null]],
    [['fax', 'FAX', null], ['tel2', 'サブ電話番号1', null]],
    [['incharge1_name', '顧客の担当者1', null], ['incharge1_phone', '顧客の担当者1電話', null]],
    [['incharge2_name', '顧客の担当者2', null], ['incharge2_phone', '顧客の担当者2電話', null]],
    [['incharge3_name', '顧客の担当者3', null], ['incharge3_phone', '顧客の担当者3電話', null]],
    [['incharge4_name', '顧客の担当者4', null], ['incharge4_phone', '顧客の担当者4電話', null]],
    [['section_name', '所属名', 20], ['sales_person', '営業担当者名', 20]],
    [['deadline', '締日', null]],
    [['notes1', '備考１', 50], ['notes2', '備考2', 50]],
    [['notes3', '備考3', 50], ['notes4', '備考4', 50]],
];
$buttonStyle = 'width:150px;' . ($isEdit ? '' : 'height:30px;') . 'cursor:pointer';
?>
<div class="panel <?= $isEdit ? 'panel-warning' : 'panel-info' ?> customer-form">
    <div class="panel-heading">
        <h3 class="panel-title"><?= h($heading) ?></h3>
    </div>
    <?= $this->Form->create($customer, ['url' => $isEdit ? ['action' => 'edit', $customer->id] : ['action' => 'add'], 'id' => 'customerForm']) ?>
    <?php if ($isEdit): ?>
        <?= $this->Form->control('id', ['type' => 'hidden']) ?>
    <?php endif; ?>
    <table class="table">
        <colgroup><col style="width:12%"><col style="width:20%"><col style="width:12%"><col style="width:20%"></colgroup>
        <tbody>
            <?php foreach ($rows as $fields): ?>
                <tr>
                    <?php foreach ($fields as [$field, $label, $maxLength]): ?>
                        <?php
                        $isNotes = str_starts_with($field, 'notes');
                        $options = [
                            'label' => false,
                            'type' => $isNotes ? 'textarea' : 'text',
                            'class' => 'form-control' . ($field === 'deadline' ? ' datepicker2' : ''),
                            'style' => 'width:100%;',
                            'required' => in_array($field, ['client_code', 'tel'], true),
                        ];
                        if ($maxLength !== null) {
                            $options['maxlength'] = $maxLength;
                        }
                        if ($isNotes) {
                            $options += ['rows' => 3, 'cols' => 5];
                        }
                        ?>
                        <td class="customer-form-label" style="vertical-align:middle;color:#ffffff;background-color:<?= $isEdit ? '#ff8c00' : '#4169E1' ?>;"><?= h($label) ?></td>
                        <td><?= $this->Form->control($field, $options) ?></td>
                    <?php endforeach; ?>
                    <?php if (count($fields) === 1): ?><td colspan="2"></td><?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
        <?php if ($isEdit && $userAuth !== null && (string)$userAuth !== '0'): ?>
            <?= $this->Form->button('削　除', ['name' => 'delete', 'value' => '1', 'type' => 'submit', 'formnovalidate' => true, 'style' => $buttonStyle, 'class' => 'btn btn-danger btn-xs', 'onclick' => 'return confirm("顧客情報を削除します。よろしいですか？")']) ?>
        <?php endif; ?>
        <?= $this->Form->button('キャンセル', ['name' => 'back', 'value' => '1', 'type' => 'submit', 'formnovalidate' => true, 'style' => $buttonStyle, 'class' => $isEdit ? 'btn btn-default btn-xs' : 'btn-default btn-xs']) ?>
        <?= $this->Form->button($isEdit ? '更　新' : '登　録', ['name' => 'add', 'value' => '1', 'type' => 'submit', 'style' => $buttonStyle, 'class' => $isEdit ? 'btn btn-warning btn-xs' : 'btn-info btn-xs']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && jQuery.fn.datepicker) {
            jQuery('.customer-form .datepicker2').datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
                dateFormat: 'yymmdd'
            });
        }
        document.querySelectorAll('#customerForm input:not([type="submit"])').forEach(function (input) {
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        });
    });
</script>
