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

            $importDirectory = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . "cmimport" . DIRECTORY_SEPARATOR;
            if (!file_exists($importDirectory)) {
                mkdir($importDirectory, 0777, true);
            }
            $importFile = $importDirectory . date("Y-m-d-H-i-s") . "-" . str_replace(" ", "", $_FILES['file']['name']);
            copy($filePath, $importFile);

            $rows = [];
            $headers = Mage::helper('importexport/Csv')->getHeaders($filePath);

            if (!in_array(Capimichi_ImportExport_Helper_ProductRow::NEW_SKU_KEY, $headers)) {
                // In questo ciclo tutti i prodotti (semplici e configurabili)
                // vengono creati, però ancora non viene definita l'associazione
                foreach (Mage::helper('importexport/Csv')->getRows($filePath) as $row) {

                    $product = Mage::helper('importexport/ProductRow')->rowToProduct($row);
                    try {
                        $product->save();
                    } catch (\Exception $exception) {
                        $response['errors'][] = [
                            'message' => $exception->getMessage(),
                            'file'    => $exception->getFile(),
                            'line'    => $exception->getLine(),
                        ];
                    }

                    $stockItem = Mage::helper('importexport/StockRow')->rowToStock($product, $row);
                    try {
                        $stockItem->save();
                    } catch (\Exception $exception) {
                        $response['errors'][] = [
                            'message' => $exception->getMessage(),
                            'file'    => $exception->getFile(),
                            'line'    => $exception->getLine(),
                        ];
                    }

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
                        $response['errors'][] = [
                            'message' => $exception->getMessage(),
                            'file'    => $exception->getFile(),
                            'line'    => $exception->getLine(),
                        ];
                    }

                    $tProduct = Mage::helper('importexport/ProductRow')->translateproduct($product, $row);

                    try {
                        $tProduct->save();
                    } catch (\Exception $exception) {
                        $response['errors'][] = [
                            'message' => $exception->getMessage(),
                            'file'    => $exception->getFile(),
                            'line'    => $exception->getLine(),
                        ];
                    }

                    $rows[] = $product->getId();
                }
                $response['products'] = $rows;

                // In questo ciclo viene definita l'associazione tra prodotti
                // configurabili e rispettive variazioni
                foreach (Mage::helper('importexport/Csv')->getRows($filePath) as $row) {

                    if (Mage::helper('importexport/ProductRow')->getRowProductType($row) == "configurable") {

                        $product = \Mage::getModel('catalog/product')->loadByAttribute('sku', Mage::helper('importexport/ProductRow')->getRowProductSku($row));

                        $childRows = [];
                        foreach (Mage::helper('importexport/Csv')->getRows($filePath) as $childRow) {
                            if (Mage::helper('importexport/ProductRow')->getRowProductParentSku($childRow) == $product->getSku()) {
                                $childRows[] = $childRow;
                            }
                        }

                        $attributeCodes = Mage::helper('importexport/ProductRow')->getConfigurableProductUsedAttributeCodes($row);

                        $product = Mage::helper('importexport/ProductRow')->setConfigurableProductUsedAttributes($product, $attributeCodes);
                        try {
                            $product->save();
                        } catch (\Exception $exception) {
                            $response['errors'][] = [
                                'message' => $exception->getMessage(),
                                'file'    => $exception->getFile(),
                                'line'    => $exception->getLine(),
                            ];
                        }

                        $product = Mage::helper('importexport/ProductRow')->setConfigurableData($product, $childRows, $attributeCodes);
                        try {
                            $product->save();
                        } catch (\Exception $exception) {
                            $response['errors'][] = [
                                'message' => $exception->getMessage(),
                                'file'    => $exception->getFile(),
                                'line'    => $exception->getLine(),
                            ];
                        }

                        $stockItem = Mage::helper('importexport/StockRow')->rowToStock($product, $row);
                        $stockItem->save();
                    }
                }
            } else {

                // Ciclo per i nuovi sku
                foreach (Mage::helper('importexport/Csv')->getRows($filePath) as $row) {

                    if (Mage::helper('importexport/ProductRow')->getRowNewSku($row)) {

                        $product = Mage::helper('importexport/ProductRow')->changeSku($row);
                        if ($product) {
                            try {
                                $product->save();
                            } catch (\Exception $exception) {
                                $response['errors'][] = [
                                    'message' => $exception->getMessage(),
                                    'file'    => $exception->getFile(),
                                    'line'    => $exception->getLine(),
                                ];
                            }
                        }
                    }
                }
            }

        } else {
            $response['MISSING FILE'];
        }

        echo json_encode($response);
    }

}