<?php

class Capimichi_ImportExport_ImportController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        // "Fetch" display
        $this->loadLayout();

        // "Inject" into display
        // THe below example will not actualy show anything since the core/template is empty
        $this->_addContent($this->getLayout()->createBlock('core/template')->setTemplate('capimichi/import_export/import/import.phtml'));

        // "Output" display
        $this->renderLayout();
    }

    public function ajaximportAction()
    {
        header('Content-Type: application/json');

        $response = [
            'status' => 'OK',
            'errors' => [],
        ];

        if (isset($_FILES['file'])) {
            $filePath = $_FILES['file']['tmp_name'];
            $rows = [];
            foreach (Mage::helper('importexport/Csv')->getRows($filePath) as $row) {

                $product = Mage::helper('importexport/ProductRow')->rowToProduct($row);
                $product->save();

                if (Mage::helper('importexport/ProductRow')->getRowProductType($row) == "configurable") {

                    $childRows = [];
                    foreach (Mage::helper('importexport/Csv')->getRows($filePath) as $childRow) {
                        if (Mage::helper('importexport/ProductRow')->getRowProductParentSku($childRow) == $product->getSku()) {
                            $childRows[] = $childRow;
                        }
                    }
                    $usedIds = Mage::helper('importexport/ProductRow')->setConfigurableProductUsedAttributes($product, $childRows);
                    $response['attribute_ids'] = $usedIds;
                    $product->save();
                }

                $stockItem = Mage::helper('importexport/StockRow')->rowToStock($product, $row);
                $stockItem->save();

                try {
                    $imageFiles = Mage::helper('importexport/ImageRow')->rowToImages($row);
                    array_reverse($imageFiles);
                    foreach ($imageFiles as $imageFile) {
                        $imageViews = [
                            "small_image",
                            "thumbnail",
                            "image",
                        ];

                        $product->addImageToMediaGallery($imageFile, $imageViews, false, false);
                    }
                    $product->save();
                } catch (\Exception $exception) {
                    $response['errors'][] = $exception->getMessage();
                }

                $rows[] = $product->getId();
            }
            $response['products'] = $rows;
        } else {
            $response['MISSING FILE'];
        }

        echo json_encode($response);
    }

}