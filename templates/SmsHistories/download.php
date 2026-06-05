<?php
/**
 * @var \App\View\AppView $this
 * @var iterable $results
 * @var array $columns
 * @var string $filename
 */

$this->Csv->renderHeaders($this->Csv->setFilename($filename));

$out = fopen('php://output', 'wb');

$header = [];
foreach ($columns as $label) {
    $header[] = mb_convert_encoding((string)$label, 'SJIS-win', 'UTF-8');
}
fputcsv($out, $header);

foreach ($results as $smsHistory) {
    $row = [];
    foreach ($columns as $field => $label) {
        $value = $smsHistory[$field] ?? '';

        if ($value instanceof \Cake\I18n\FrozenTime || $value instanceof \Cake\I18n\FrozenDate) {
            $value = $value->format('Y-m-d H:i:s');
        }

        $row[] = mb_convert_encoding((string)$value, 'SJIS-win', 'UTF-8');
    }
    fputcsv($out, $row);
}

fclose($out);
