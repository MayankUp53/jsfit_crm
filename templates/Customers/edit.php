<?php
$this->assign('title', '顧客詳細情報 更新');
?>
<?= $this->element('Customers/form', ['customer' => $customer, 'isEdit' => true]) ?>
