<?php
$this->assign('title', '顧客詳細情報新規登録');
?>
<?= $this->element('Customers/form', ['customer' => $customer, 'isEdit' => false]) ?>
