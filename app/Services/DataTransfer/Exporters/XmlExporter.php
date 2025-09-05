<?php

namespace App\Services\DataTransfer\Exporters;

use SimpleXMLElement;

class XmlExporter extends AbstractExporter
{
    public function export(iterable $data): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rows/>');
        foreach ($data as $item) {
            $arr = is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : (array) $item;
            $rowNode = $xml->addChild('row');
            $this->arrayToXml($arr, $rowNode);
        }
        
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    private function arrayToXml(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            $key = is_numeric($key) ? "item{$key}" : preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $key);
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }

    public function extension(): string { return 'xml'; }
    public function mime(): string { return 'application/xml'; }
}
