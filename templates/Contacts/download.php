<?php
/**
 * @var \App\View\AppView $this
 * @var iterable $results
 * @var array $columns   // associative: field => label
 * @var string $filename
 */

$this->Csv->renderHeaders($this->Csv->setFilename($filename));

$out = fopen('php://output', 'wb');

// Header row (labels)
$header = [];
foreach ($columns as $label) {
    $header[] = mb_convert_encoding((string)$label, 'SJIS-win', 'UTF-8');
}
fputcsv($out, $header);

// Data rows
foreach ($results as $contact) {
    $row = [];
    foreach ($columns as $field => $label) {
        $value = $contact[$field] ?? '';

        if ($value instanceof \Cake\I18n\FrozenTime || $value instanceof \Cake\I18n\FrozenDate) {
            $value = $value->format('Y-m-d H:i:s');
        }

        // Keep newlines; fputcsv will quote the field properly
        $row[] = mb_convert_encoding((string)$value, 'SJIS-win', 'UTF-8');
    }
    fputcsv($out, $row);
}

fclose($out);
