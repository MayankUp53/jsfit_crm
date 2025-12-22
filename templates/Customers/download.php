<?php
/**
 * @var \App\View\AppView $this
 * @var array $columns
 * @var string $filename
 * @var iterable $results
 * @var array $sexLabels
 */
$this->Csv->renderHeaders($this->Csv->setFilename($filename));

$row = [];
foreach ($columns as $label) {
    $row[] = $this->Csv->addField2(mb_convert_encoding($label, 'SJIS-win', 'UTF-8'));
}
echo implode(',', $row) . "\n";

foreach ($results as $customer) {
    $row = [];
    foreach ($columns as $field => $label) {
        $value = $customer->{$field} ?? '';
        if ($field === 'sex') {
            $value = $sexLabels[$value] ?? $value;
        }
        if ($value instanceof \Cake\I18n\FrozenTime || $value instanceof \Cake\I18n\FrozenDate) {
            $value = $value->format('Y-m-d H:i:s');
        }
        $row[] = $this->Csv->addField(mb_convert_encoding((string)$value, 'SJIS-win', 'UTF-8'));
    }
    echo implode(',', $row) . "\n";
}
