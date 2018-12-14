<?php

class Capimichi_ImportExport_Helper_ProductRow extends Mage_Core_Helper_Abstract
{
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

    /**
     * @param $row
     * @return mixed
     */
    public function rowToSimpleProduct($row)
    {
        $title = empty($row[self::TITLE_KEY]) ? "" : $row[self::TITLE_KEY];
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
            $product->setTypeId('simple');
            $product->setCreatedAt(strtotime('now'));
            $product->setStoreId(\Mage::app()->getStore()->getId());
        }

        foreach ($row as $key => $value) {

            if (substr($key, 0, 4) == "att_") {

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

        $product->setName($title);
        $product->setStatus($status);
        $product->setWeight($weight);
        if ($height) {
            $product->setHeight($height);
        }
        $product->setTaxClassId($taxClassId);
        $product->setVisibility($visibility);
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
            "riferimento",
            "tipo",
            "categoria",
//            "att_ebay_sku",
//            "att_finitura",
//            "att_componeni_aggiuntivi",
//            "att_fondello",
            "descrizione",
//            "att_manufacturer",
            "titolo",
//            "att_marca_moto",
//            "att_modello_moto",
            "prezzo",
            "prezzo_speciale",
            "attivo",
            "peso",
            "quantità",
            "immagini",
            "testo_magazzino",
            "testo_no_magazzino",
            "genitore",
            "immagini",
        ];

        foreach ($attributeCodes as $attributeCode) {
            $headers[] = "att_" . $attributeCode;
        }

        return $headers;
    }

    public function simpleProductToRow($product, $attributeCodes)
    {
        $row = [
            $product->getSku(),
            "simple",
            implode("|", $product->getCategoryIds()),
            "descrizione",
            "titolo",
            $product->getPrice(),
            $product->getSpecialPrice(),
            "attivo",
            "peso",
            "quantità",
            "immagini",
            "testo_magazzino",
            "testo_no_magazzino",
            "genitore",
        ];

        $imageUrls = [];
        foreach ($product->getMediaGalleryImages() as $image) {
            $imageUrls[] = $image->getUrl();
        }
        $row[] = implode("|", $imageUrls);


        foreach ($attributeCodes as $attributeCode) {
            $row[] = $product->getData($attributeCode);
        }

        return $row;
    }
}