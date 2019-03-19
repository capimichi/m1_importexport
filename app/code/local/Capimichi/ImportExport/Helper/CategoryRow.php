<?php

class Capimichi_ImportExport_Helper_CategoryRow extends Mage_Core_Helper_Abstract
{
    const NAME_KEY = "nome_categorie";
    const SKU_KEY  = "riferimento";
    
    /**
     * @param $row
     *
     * @return mixed
     */
    public function rowToCategories($row)
    {
        $names = empty($row[self::NAME_KEY]) ? [] : explode("|", $row[self::NAME_KEY]);
        $sku = empty($row[self::SKU_KEY]) ? null : $row[self::SKU_KEY];
        
        $nameGroupsCategories = [];
        
        foreach ($names as $nameGroup) {
            
            $nameGroupItems = explode(">", $nameGroup);
            
            $nameGroupCategories = [];
            
            foreach ($nameGroupItems as $index => $name) {
                
                $name = trim($name);
                
                if (!$index) {
                    $parent = Mage::getModel('catalog/category')->load(2);
                } else {
                    $parent = $nameGroupCategories[$index - 1];
                }
                
                $category = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToFilter('parent_id', $parent->getId())
                    ->addFieldToFilter('name', $name)
                    ->getFirstItem();
                
                if (!$category->getId()) {
                    $category = Mage::getModel('catalog/category');
                    $category->setName($name);
                    $category->setIsActive(1);
                    $category->setDisplayMode('PRODUCTS');
                    $category->setIsAnchor(1);
                    $category->setPath($parent->getPath());
                    $category->setParentId($parent->getId());
                    $category->setStoreId(Mage::app()->getStore()->getId());
                    $category->save();
                }
                
                $nameGroupCategories[] = $category;
            }
            
            $nameGroupsCategories[] = $nameGroupCategories;
        }
        
        return $nameGroupsCategories;
    }
    
    public function getRowHeader($attributeCodes)
    {
        $headers = [
            Capimichi_ImportExport_Helper_ProductRow::SKU_KEY,
            Capimichi_ImportExport_Helper_ProductRow::CATEGORY_KEY,
        ];
        
        return $headers;
    }
    
    public function productToRow($product, $attributeCodes, $includeImages = true)
    {
        $product = \Mage::getModel('catalog/product')->load($product->getId());
        
        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
            ->getAttributeCollection()
            ->addSetInfo();
        
        $imageUrls = [];
        if (count($product->getMediaGalleryImages()) && $includeImages) {
            foreach ($product->getMediaGalleryImages() as $image) {
                $imageUrls[] = $image->getUrl();
            }
        }
        
        $usedProductAttributeCodes = [];
        if ($product->getTypeId() == "configurable") {
            $usedProductAttributes = $product->getTypeInstance()->getUsedProductAttributes($product);
            foreach ($usedProductAttributes as $attribute) {
                $usedProductAttributeCodes[] = $attribute->getAttributeCode();
            }
        }
        
        
        $parentSku = "";
        if ($product->getTypeId() == "simple") {
            $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
            if (!$parentIds) {
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            }
            if (isset($parentIds[0])) {
                $parent = Mage::getModel('catalog/product')->load($parentIds[0]);
                $parentSku = $parent->getSku();
            }
        }
        
        $row = [
            $product->getSku(),
            $product->getTypeId(),
            implode("|", $product->getCategoryIds()),
            $product->getDescription(),
            $product->getName(),
            $product->getPrice(),
            $product->getSpecialPrice(),
            $product->getStatus() == 2 ? 0 : $product->getStatus(),
            $product->getWeight(),
            $product->getVisibility(),
            $product->getStockItem()->getManageStock() ? 1 : 0,
            $product->getStockItem()->getQty(),
            $product->getStockItem()->getData('is_in_stock'),
            implode("|", $imageUrls),
            implode("|", $usedProductAttributeCodes),
            $parentSku,
        ];
        
        
        foreach ($attributeCodes as $attributeCode) {
            
            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
            foreach ($attributes as $attribute) {
                if ($attributeCode == $attribute->getName()) {
                    
                    $type = $attribute->getData('frontend_input');
                    
                    switch ($type) {
                        case "select":
                            $row[] = $product->getAttributeText($attributeCode);
                            break;
                        case "text":
                            $row[] = $product->getData($attributeCode);
                            break;
                        default:
                            $row[] = $product->getData($attributeCode);
                            break;
                    }
                }
            }
        }
        
        return $row;
    }
}