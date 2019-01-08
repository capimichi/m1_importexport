<?php

class Capimichi_ImportExport_Helper_Csv extends Mage_Core_Helper_Abstract
{
    /**
     * @param $csvPath
     * @return Generator
     */
    public function getRows($csvPath)
    {
        $f = fopen($csvPath, "r");
        $headers = fgetcsv($f);
        while (!feof($f)) {
            $row = fgetcsv($f);
            $item = [];
            foreach ($headers as $key => $headerName) {
                $item[$headerName] = $row[$key];
            }
            if (!feof($f)) {
                yield $item;
            }
        }
    }

    /**
     * @param $csvPath
     * @return array
     */
    public function getHeaders($csvPath)
    {
        $f = fopen($csvPath, "r");
        $headers = fgetcsv($f);
        fclose($f);
        return $headers;
    }
}