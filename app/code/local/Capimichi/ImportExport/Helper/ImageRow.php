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
            rename($tempImagePath, $imagePath);
            $imagePaths[] = $imagePath;
        }

        return $imagePaths;

    }
}