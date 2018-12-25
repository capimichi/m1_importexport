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
        $images = empty($row[self::IMAGES_KEY]) ? [] : explode("|", $row[self::IMAGES_KEY]);
        $imagesDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . "import" . DIRECTORY_SEPARATOR;
        if (!file_exists($imagesDir)) {
            mkdir($imagesDir, 0777, true);
        }

        $imagePaths = [];

        foreach ($images as $image) {

            $slug = implode("_", [
                'cmimportimage',
                md5($image),
            ]);

            if (
                !preg_match("/^http:\/\//is", $image)
                && !preg_match("/^https:\/\//is", $image)
            ) {
                $image = $imagesDir . ltrim($image, "/");
            }
            $content = file_get_contents($image);
            $tempImagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $slug;
            file_put_contents($tempImagePath, $content);
            $info = getimagesize($tempImagePath);
            $extension = image_type_to_extension($info[2]);
            $imagePath = $tempImagePath . "." . $extension;
            rename($tempImagePath, $imagePath);
            $imagePaths[] = $imagePath;
        }

        return $imagePaths;

    }
}