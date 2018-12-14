<?php

/**
 * Created by PhpStorm.
 * User: michelecapicchioni
 * Date: 11/12/18
 * Time: 16:39
 */
class Capimichi_ImportExport_Helper_ImageRow extends Mage_Core_Helper_Abstract
{
    const IMAGES_KEY = "immagini";

    public function test()
    {
        return "SI";
    }

    public function rowToImages($row)
    {
//        $product = \Mage::getModel('catalog/product')->load($product->getId());

        $imageUrls = empty($row[self::IMAGES_KEY]) ? [] : explode("|", $row[self::IMAGES_KEY]);

        $imagePaths = [];

        foreach ($imageUrls as $imageUrl) {

            $slug = implode("_", [
                'cmimportimage',
                md5($imageUrl),
            ]);
            $content = file_get_contents($imageUrl);
            $tempImagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $slug;
            file_put_contents($tempImagePath, $content);
            $info = getimagesize($tempImagePath);
            $extension = image_type_to_extension($info[2]);
            $imagePath = $tempImagePath . "." . $extension;
            file_put_contents($imagePath, $content);
            $imagePaths[] = $imagePath;
            unlink($tempImagePath);

//            $slug = rand(0, 9999999999) . strtotime("now");
//            $content = file_get_contents($imageUrl);
//            $imageDir = \Mage::getBaseDir('media') . DS . 'import' . DS;
//            $imageDir .= substr($slug, 0, 2) . DS . substr($slug, 2, 2) . DS;
//            if (!file_exists($imageDir)) {
//                mkdir($imageDir, 0777, true);
//            }
//            $imageTempPath = $imageDir . $slug;
//            file_put_contents($imageTempPath, $content);
//            $info = getimagesize($imageTempPath);
//            $extension = image_type_to_extension($info[2]);
//            $importedImagePath = $imageDir . $slug . "." . $extension;
//            move_uploaded_file($imageTempPath, $importedImagePath);
//            $imagePaths[] = $imagePath;
        }

        return $imagePaths;

    }
}