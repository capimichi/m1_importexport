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
            if(!feof($f)) {
                yield $item;
            }
            yield $item;
        }
    }
}