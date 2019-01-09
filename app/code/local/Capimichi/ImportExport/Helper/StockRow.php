<?php

/**
 * Created by PhpStorm.
 * User: michelecapicchioni
 * Date: 11/12/18
 * Time: 16:39
 */
class Capimichi_ImportExport_Helper_StockRow extends Mage_Core_Helper_Abstract
{
//    const SKU_KEY = "riferimento";
    const QUANTITY_KEY = "quantità";
    const MANAGE_QUANTITY_KEY = "gestisci_quantità";
    const AVAILABLE_KEY = "disponibile";

    public function rowToStock($product, $row)
    {
        $product = \Mage::getModel('catalog/product')->load($product->getId());
        $quantity = (!isset($row[self::QUANTITY_KEY]) || $row[self::QUANTITY_KEY] == "") ? -1 : intval($row[self::QUANTITY_KEY]);
        $manageStock = (!isset($row[self::MANAGE_QUANTITY_KEY]) || (isset($row[self::QUANTITY_KEY]) && $row[self::QUANTITY_KEY] == "")) ? -1 : intval($row[self::MANAGE_QUANTITY_KEY]);
        $available = (!isset($row[self::AVAILABLE_KEY]) || $row[self::QUANTITY_KEY] == "") ? -1 : intval($row[self::AVAILABLE_KEY]);

        if ($product) {

            $stockItem = $product->getStockItem();
            if (!$stockItem) {
                $stockItem = \Mage::getModel('cataloginventory/stock_item');
                $stockItem->assignProduct($product);
            }
            $stockItem->setData('use_config_manage_stock', 0);

            $stockItem->setData('store_id', 1);
            $stockItem->setData('stock_id', 1);

            if ($manageStock != -1) {
                $stockItem->setData('manage_stock', $manageStock);
            }

            if ($stockItem->getManageStock()) {

                if ($quantity != -1) {
                    $stockItem->setData('qty', $quantity);
                }

                if ($available != -1) {
                    $stockItem->setData('is_in_stock', $available);
                }
            }

            return $stockItem;
        }

        return null;

    }
}