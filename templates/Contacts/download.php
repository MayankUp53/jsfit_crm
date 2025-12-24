<?php
/**
 * @var iterable $results
 * @var array $columns
 */
$this->Csv->renderHeaders($this->Csv->setFilename($filename));

// Output header row
$row = [];
foreach ($columns as $label) {
    $row[] = $this->Csv->addField2(mb_convert_encoding($label, 'SJIS-win', 'UTF-8'));
}
echo implode(',', $row) . "\n";

// Output data rows
foreach ($results as $contact) {
    $row = [];
    foreach ($columns as $field => $label) {
        $value = $contact[$field] ?? '';
        if ($value instanceof \Cake\I18n\FrozenTime || $value instanceof \Cake\I18n\FrozenDate) {
            $value = $value->format('Y-m-d H:i:s');
        }
        $row[] = $this->Csv->addField(mb_convert_encoding((string)$value, 'SJIS-win', 'UTF-8'));
    }
    echo implode(',', $row) . "\n";
}
