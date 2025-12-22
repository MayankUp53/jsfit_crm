<?php
/**
 * @var iterable $results
 * @var array $columns
 */
$this->Csv->renderHeaders($this->Csv->setFilename($filename));

$row = [];
foreach ($columns as $label) {
    $row[] = $this->Csv->addField2(mb_convert_encoding($label, 'SJIS-win', 'UTF-8'));
}
echo implode(',', $row) . "\n";

foreach ($results as $contact) {
    $row = [];
    foreach ($columns as $field => $label) {
        if (str_contains($field, '.')) {
            [$relation, $col] = explode('.', $field, 2);
            $value = $contact->{$relation}->{$col} ?? '';
        } else {
            $value = $contact->{$field} ?? '';
        }
        if ($value instanceof \Cake\I18n\FrozenTime || $value instanceof \Cake\I18n\FrozenDate) {
            $value = $value->format('Y-m-d H:i:s');
        }
        $row[] = $this->Csv->addField(mb_convert_encoding((string)$value, 'SJIS-win', 'UTF-8'));
    }
    echo implode(',', $row) . "\n";
}
