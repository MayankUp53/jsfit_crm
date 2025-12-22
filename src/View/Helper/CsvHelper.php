<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * CSV Helper
 * 
 * Helper for generating CSV files with proper encoding
 */
class CsvHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'delimiter' => ',',
        'enclosure' => '"',
    ];

    /**
     * Add a field to the CSV line
     *
     * @param string $value Field value
     * @return string Encoded field value
     */
    public function addField(string $value): string
    {
        return mb_convert_encoding('"' . $value . '"', 'SJIS-win', 'UTF-8');
    }

    /**
     * Add a field without quotes
     *
     * @param string $value Field value
     * @return string Encoded field value
     */
    public function addField2(string $value): string
    {
        return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
    }

    /**
     * Render CSV headers
     *
     * @param string $filename Filename
     * @return void
     */
    public function renderHeaders(string $filename): void
    {
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=" . $filename);
    }

    /**
     * Set filename with .csv extension if needed
     *
     * @param string $filename Filename
     * @return string Filename with .csv extension
     */
    public function setFilename(string $filename): string
    {
        if (strtolower(substr($filename, -4)) != '.csv') {
            $filename .= '.csv';
        }
        return $filename;
    }
}

