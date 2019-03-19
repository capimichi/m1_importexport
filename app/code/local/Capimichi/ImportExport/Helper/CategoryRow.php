<?php

class Capimichi_ImportExport_Helper_CategoryRow extends Mage_Core_Helper_Abstract
{
    const NAME_KEY = "nome_categorie";
    const SKU_KEY  = "riferimento";
    
    /**
     * @param $row
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function rowToCategories($row)
    {
        $names = empty($row[self::NAME_KEY]) ? [] : explode("|", $row[self::NAME_KEY]);
        
        $nameGroupsCategories = [];
        
        $rootCategory = Mage::getModel('catalog/category')->load(2);
        
        foreach ($names as $nameGroup) {
            
            $nameGroupItems = explode(">", $nameGroup);
            
            $nameGroupCategories = [];
            
            foreach ($nameGroupItems as $index => $name) {
                
                $name = trim($name);
                
                if (!$index) {
                    $parent = $rootCategory;
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
    
    public function categoriesToRow($sku, $categoriesGroups)
    {
        $categoryIds = [];
        foreach ($categoriesGroups as $categoriesGroup) {
            if (count($categoriesGroup)) {
                $category = array_pop($categoriesGroup);
                $categoryIds[] = $category->getId();
            }
        }
        $row = [
            $sku,
            implode("|", $categoryIds),
        ];
        
        return $row;
    }
}