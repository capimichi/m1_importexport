<?php

class Capimichi_ImportExport_Helper_ProductRow extends Mage_Core_Helper_Abstract
{
    const TITLE_KEY = "titolo";
    const SKU_KEY = "riferimento";

    /**
     * @param $row
     */
    public function rowToSimpleProduct($row, $save = false)
    {
        $title = $row[self::TITLE_KEY];
        $sku = $row[self::SKU_KEY];
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if (!$product) {
            $product = Mage::getModel('catalog/product');
        }
        $product->setTitle($title);
        if ($save) {
            $product->save();
        }

        return $product;
    }
}