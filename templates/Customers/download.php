<?php
/**
 * @var \App\View\AppView $this
 * @var array $columns
 * @var string $filename
 * @var iterable $results
 * @var array $sexLabels
 */

$this->Csv->renderHeaders($this->Csv->setFilename($filename));

// Excel (JP) likes CRLF and SJIS-win
$out = fopen('php://output', 'wb');

// 1) Header row
$header = [];
foreach ($columns as $label) {
    $header[] = mb_convert_encoding((string)$label, 'SJIS-win', 'UTF-8');
}
fputcsv($out, $header);

// 2) Data rows
foreach ($results as $customer) {
    $row = [];
    foreach ($columns as $field => $label) {
        $value = is_array($customer)
            ? ($customer[$field] ?? '')
            : ($customer->{$field} ?? '');

        if ($field === 'sex') {
            $value = $sexLabels[$value] ?? $value;
        }

        if ($value instanceof \Cake\I18n\FrozenTime || $value instanceof \Cake\I18n\FrozenDate) {
            $value = $value->format('Y-m-d H:i:s');
        }

        $row[] = mb_convert_encoding((string)$value, 'SJIS-win', 'UTF-8');
    }
    fputcsv($out, $row);
}

fclose($out);
