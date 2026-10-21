<?php

declare(strict_types=1);

namespace App\Services;

use Generator;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;
use ZipArchive;

class StudentMasterReader
{
    public function rows(string $path): Generator
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Upload an Excel .xlsx workbook.');
        }
        try {
            if ($zip->locateName('xl/workbook.xml') !== false) {
                foreach ($this->elements($zip, 'xl/workbook.xml', 'workbookPr') as $properties) {
                    if (in_array(strtolower((string) $properties['date1904']), ['1', 'true'], true)) {
                        throw new RuntimeException('Convert the workbook to the standard 1900 date system before importing.');
                    }
                }
            }
            $strings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach ($this->elements($zip, 'xl/sharedStrings.xml', 'si') as $item) {
                    $strings[] = $this->text($item);
                }
            }
            $headers = null;
            foreach ($this->elements($zip, 'xl/worksheets/sheet1.xml', 'row') as $row) {
                $values = [];
                foreach ($row->xpath('./*[local-name()="c"]') as $cell) {
                    $column = 0;
                    foreach (str_split(preg_replace('/[0-9]/', '', (string) $cell['r'])) as $letter) {
                        $column = $column * 26 + ord($letter) - 64;
                    }
                    $valueNodes = $cell->xpath('./*[local-name()="v"]');
                    $value = isset($valueNodes[0]) ? (string) $valueNodes[0] : '';
                    if ((string) $cell['t'] === 's') {
                        $value = $strings[(int) $value] ?? '';
                    } elseif ((string) $cell['t'] === 'inlineStr') {
                        $value = $this->text($cell);
                    }
                    $values[$column - 1] = trim($value);
                }
                if (!$values || !array_filter($values, 'strlen')) {
                    continue;
                }
                if ($headers === null) {
                    ksort($values);
                    $headers = $values;
                    if (!in_array('fldStudNo', $headers, true) || !in_array('fldLastName', $headers, true)
                        || !in_array('fldFirstName', $headers, true) || !in_array('fldActive', $headers, true)) {
                        throw new RuntimeException('The first worksheet must contain the original StudentMaster column headings.');
                    }
                    continue;
                }
                $fields = [];
                $source = [];
                foreach ($headers as $index => $heading) {
                    $value = $values[$index] ?? '';
                    $source[] = ['column' => $index + 1, 'heading' => $heading, 'value' => $value];
                    // Duplicate headings (including both email columns) remain separate in source.
                    if (!isset($fields[$heading]) || $fields[$heading] === '') {
                        $fields[$heading] = $value;
                    }
                }
                yield ['row' => (int) $row['r'], 'fields' => $fields, 'source' => $source];
            }
            if ($headers === null) {
                throw new RuntimeException('The workbook is empty.');
            }
        } finally {
            $zip->close();
        }
    }

    private function text(SimpleXMLElement $element): string
    {
        return implode('', array_map('strval', $element->xpath('.//*[local-name()="t"]')));
    }

    private function elements(ZipArchive $zip, string $entry, string $element): Generator
    {
        $stat = $zip->statName($entry);
        if (!$stat || $stat['size'] > 64 * 1024 * 1024) {
            throw new RuntimeException('The workbook is missing a worksheet or exceeds the 64 MB expanded XML limit.');
        }
        $stream = $zip->getStream($entry);
        $temporary = tmpfile();
        if (!$stream || !$temporary) {
            throw new RuntimeException('Unable to read the workbook.');
        }
        $xml = new XMLReader();
        $previousErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();
        try {
            stream_copy_to_stream($stream, $temporary);
            $xml->open(stream_get_meta_data($temporary)['uri'], null, LIBXML_NONET | LIBXML_COMPACT);
            while ($xml->read()) {
                if ($xml->nodeType === XMLReader::DOC_TYPE) {
                    throw new RuntimeException('Workbooks containing XML document types are not supported.');
                }
                if ($xml->nodeType === XMLReader::ELEMENT && $xml->localName === $element) {
                    $node = simplexml_load_string($xml->readOuterXml(), SimpleXMLElement::class, LIBXML_NONET);
                    if (!$node) {
                        throw new RuntimeException('The workbook contains invalid XML.');
                    }
                    yield $node;
                }
            }
            foreach (libxml_get_errors() as $error) {
                if ($error->level >= LIBXML_ERR_ERROR) {
                    throw new RuntimeException('The workbook contains malformed XML.');
                }
            }
        } finally {
            $xml->close();
            fclose($stream);
            fclose($temporary);
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }
    }
}
