<?php

class Capimichi_ImportExport_Helper_ProductRow extends Mage_Core_Helper_Abstract
{
    const TYPE_KEY = "tipo";
    const TITLE_KEY = "titolo";
    const SKU_KEY = "riferimento";
    const STATUS_KEY = "attivo";
    const WEIGHT_KEY = "peso";
    const HEIGHT_KEY = "altezza";
    const TAX_CLASS_KEY = "tassa";
    const VISIBILITY_KEY = "visibilita";
    const DESCRIPTION_KEY = "descrizione";
    const SHORT_DESCRIPTION_KEY = "descrizione_breve";
    const PRICE_KEY = "prezzo";
    const SPECIAL_PRICE_KEY = "prezzo_speciale";
    const CATEGORY_KEY = "categoria";
    const ATTRIBUTE_SET_KEY = "set_attributi";
    const META_TITLE_KEY = "meta_titolo";
    const META_DESCRIPTION_KEY = "meta_descrizione";
    const PARENT_SKU_KEY = "genitore";
    const CONFIGURABLE_ATTRIBUTES_KEY = "attributi_variazioni";

    /**
     * @param $row
     * @return mixed
     */
    public function getRowProductType($row)
    {
        return isset($row[self::TYPE_KEY]) ? $row[self::TYPE_KEY] : 'simple';
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getRowProductParentSku($row)
    {
        return isset($row[self::PARENT_SKU_KEY]) ? $row[self::PARENT_SKU_KEY] : null;
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getRowProductSku($row)
    {
        return isset($row[self::SKU_KEY]) ? $row[self::SKU_KEY] : null;
    }

    /**
     * @param $row
     * @return array
     */
    public function getConfigurableProductUsedAttributeCodes($row)
    {
        return isset($row[self::CONFIGURABLE_ATTRIBUTES_KEY]) ? explode("|", $row[self::CONFIGURABLE_ATTRIBUTES_KEY]) : [];
    }

    /**
     * @param $products
     * @return array
     */
    public function getProductsUsedAttributeCodes($products)
    {
        $attributeCodes = [];
        foreach ($products as $product) {
            if ($product->type_id == 'configurable') {
                $usedProductAttributes = $product->getTypeInstance()->getUsedProductAttributes($product);
                foreach ($usedProductAttributes as $attribute) {
                    $attributeCodes[] = $attribute->getAttributeCode();
                }
            }
        }
        $attributeCodes = array_unique($attributeCodes);

        return $attributeCodes;
    }

    /**
     * @param $product
     * @param $attributeCodes
     * @return Mage_Core_Model_Abstract
     */
    public function setConfigurableProductUsedAttributes($product, $attributeCodes)
    {
        $product = \Mage::getModel('catalog/product')->load($product->getId());

        $product->setCanSaveConfigurableAttributes(true);
        $product->setCanSaveCustomOptions(true);

        $attributeIds = [];

        foreach ($attributeCodes as $attributeCode) {
            $attributeIds[] = \Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', $attributeCode);
        }

        $product->getTypeInstance()->setUsedProductAttributeIds($attributeIds);
        $product->setConfigurableAttributesData($product->getTypeInstance()->getConfigurableAttributesAsArray());

        return $product;
    }

    public function setConfigurableData($product, $rows, $attributeCodes)
    {
        $product = \Mage::getModel('catalog/product')->load($product->getId());

        $configurableProductsData = $product->getConfigurableProductsData();

        foreach ($rows as $row) {

            $variationProduct = \Mage::getModel('catalog/product')->loadByAttribute('sku', $this->getRowProductSku($row));
            $simpleProductsData = [];

            foreach ($attributeCodes as $attributeCode) {

                $attributeId = \Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', $attributeCode);

                $attributeValue = $row['att_' . $attributeCode];

                $simpleProductsData[] = [
                    'label'         => rand(0, 999999),
                    'attribute_id'  => intval($attributeId),
                    'value_index'   => $variationProduct->getResource()->getAttribute($attributeCode)->getSource()->getOptionId($attributeValue),
                    'is_percent'    => 0,
                    'pricing_value' => floatval($variationProduct->getPrice()),
                ];
            }

            $configurableProductsData[$variationProduct->getId()] = $simpleProductsData;

            //            $configurableAttributesData[0]['values'][] = $simpleProductsData;

            $variations[] = $variationProduct;
        }

        $product->setConfigurableProductsData($configurableProductsData);

        return $product;
    }

    /**
     * @param $row
     * @return mixed
     */
    public function rowToProduct($row)
    {
        $title = empty($row[self::TITLE_KEY]) ? "" : $row[self::TITLE_KEY];
        $type = empty($row[self::TYPE_KEY]) ? "simple" : $row[self::TYPE_KEY];
        $sku = empty($row[self::SKU_KEY]) ? "" : $row[self::SKU_KEY];
        $status = empty($row[self::STATUS_KEY]) ? 1 : $row[self::STATUS_KEY];
        $weight = empty($row[self::WEIGHT_KEY]) ? 0 : $row[self::WEIGHT_KEY];
        $height = empty($row[self::HEIGHT_KEY]) ? 0 : $row[self::HEIGHT_KEY];
        $taxClassId = empty($row[self::TAX_CLASS_KEY]) ? 5 : $row[self::TAX_CLASS_KEY];
        $visibility = empty($row[self::VISIBILITY_KEY]) ? 4 : $row[self::VISIBILITY_KEY];
        $description = empty($row[self::DESCRIPTION_KEY]) ? "" : $row[self::DESCRIPTION_KEY];
        $shortDescription = empty($row[self::SHORT_DESCRIPTION_KEY]) ? "" : $row[self::SHORT_DESCRIPTION_KEY];
        $price = empty($row[self::PRICE_KEY]) ? 0 : $row[self::PRICE_KEY];
        $price = str_replace(",", ".", $price);
        $specialPrice = empty($row[self::SPECIAL_PRICE_KEY]) ? 0 : $row[self::SPECIAL_PRICE_KEY];
        $categories = empty($row[self::CATEGORY_KEY]) ? [] : explode("|", $row[self::CATEGORY_KEY]);
        $attributeSet = empty($row[self::ATTRIBUTE_SET_KEY]) ? 4 : $row[self::ATTRIBUTE_SET_KEY];
        $metaTitle = empty($row[self::META_TITLE_KEY]) ? "" : $row[self::META_TITLE_KEY];
        $metaDescription = empty($row[self::META_DESCRIPTION_KEY]) ? "" : $row[self::META_DESCRIPTION_KEY];

        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
            ->getAttributeCollection()
            ->addSetInfo();

        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if (!$product) {
            $product = Mage::getModel('catalog/product');
            $product->setSku($sku);
            $product->setWebsiteIds([1]);
            $product->setTypeId($type);
            $product->setCreatedAt(strtotime('now'));
            $product->setStoreId(\Mage::app()->getStore()->getId());
        }

        foreach ($row as $key => $value) {

            if (preg_match("/^att_/is", $key)) {

                $attributeName = preg_replace("/^att_/is", '', $key);

                $type = null;
                /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
                foreach ($attributes as $attribute) {
                    if ($attributeName == $attribute->getName()) {
                        $type = $attribute->getData('frontend_input');
                    }
                }

                switch ($type) {
                    case "select":
                        $product->setData($attributeName, $product->getResource()->getAttribute($attributeName)->getSource()->getOptionId($value));
                        break;
                    case "text":
                        $product->setData($attributeName, $value);
                        break;
                }
            }
        }

        if ($title == "") {
            $title = ".";
        }
        $product->setName($title);
        $product->setStatus($status);
        $product->setWeight($weight);
        if ($height) {
            $product->setHeight($height);
        }
        $product->setTaxClassId($taxClassId);
        $product->setVisibility($visibility);

        if ($description == "") {
            $description = ".";
        }
        $product->setDescription($description);
        $product->setPrice($price);
        if ($specialPrice) {
            $product->setSpecialPrice($specialPrice);
        }
        $product->setCategoryIds($categories);
        $product->setDescription($description);

        if ($shortDescription) {
            $product->setShortDescription($shortDescription);
        }
        $product->setAttributeSetId($attributeSet);
        $product->setMetaTitle($metaTitle);
        $product->setMetaDescription($metaDescription);

        return $product;
    }

    public function getRowHeader($attributeCodes)
    {
        $headers = [
            self::SKU_KEY,
            self::TYPE_KEY,
            self::CATEGORY_KEY,
            self::DESCRIPTION_KEY,
            self::TITLE_KEY,
            self::PRICE_KEY,
            self::SPECIAL_PRICE_KEY,
            self::STATUS_KEY,
            self::WEIGHT_KEY,
            self::VISIBILITY_KEY,
            Capimichi_ImportExport_Helper_StockRow::MANAGE_QUANTITY_KEY,
            Capimichi_ImportExport_Helper_StockRow::QUANTITY_KEY,
            Capimichi_ImportExport_Helper_ImageRow::IMAGES_KEY,
            self::CONFIGURABLE_ATTRIBUTES_KEY,
            self::PARENT_SKU_KEY,
        ];

        foreach ($attributeCodes as $attributeCode) {
            $headers[] = "att_" . $attributeCode;
        }

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
                    }
                }
            }
        }

        return $row;
    }
}