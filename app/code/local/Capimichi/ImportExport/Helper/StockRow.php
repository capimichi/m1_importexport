<?php

/**
 * Created by PhpStorm.
 * User: michelecapicchioni
 * Date: 11/12/18
 * Time: 16:39
 */
class Capimichi_ImportExport_Helper_StockRow extends Mage_Core_Helper_Abstract
{
    const SKU_KEY = "riferimento";
    const QUANTITY_KEY = "quantità";

    public function rowToStock($row)
    {
        $sku = empty($row[self::SKU_KEY]) ? "" : $row[self::SKU_KEY];
        $quantity = empty($row[self::QUANTITY_KEY]) ? "" : $row[self::QUANTITY_KEY];
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if ($product) {

            $stockItem = $product->getStockItem();
            if (!$stockItem) {
                $stockItem = \Mage::getModel('cataloginventory/stock_item');
                $stockItem->assignProduct($product);
            }
            $stockItem->setData('store_id', 1);
            $stockItem->setData('stock_id', 1);
            $stockItem->setData('qty', $quantity);

            return $stockItem;
        }

        return null;

    }
}