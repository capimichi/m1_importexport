<?php

class Capimichi_ImportExport_ExportController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        // "Fetch" display
        $this->loadLayout();

        // "Inject" into display
        // THe below example will not actualy show anything since the core/template is empty
        $this->_addContent($this->getLayout()->createBlock('core/template')->setTemplate('capimichi/import_export/export/export.phtml'));

        // echo "Hello developer...";

        // "Output" display
        $this->renderLayout();
    }

    public function exportAction()
    {
        $manufacturer = isset($_POST['manufacturer']) ? $_POST['manufacturer'] : null;
        $includeImages = isset($_POST['images']) ? true : false;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $pageSize = 500;

        $attributeCodes = [];
        foreach ($_POST as $postKey => $postValue) {
            if (substr($postKey, 0, 4) == "att_") {
                $attributeCodes[] = preg_replace("/^att_/is", "", $postKey);
            }
        }

        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . implode("-", [
                'cm-export',
                strtotime('now'),
                '.csv',
            ]);
        $f = fopen($filePath, "w");

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', 4)
            ->setPageSize($pageSize)
            ->setCurPage($page);

        if ($manufacturer) {
            $products
                ->addAttributeToFilter('manufacturer', array('eq' => $manufacturer));
        }

        $exportedIds = [];

        fputcsv($f, Mage::helper('importexport/ProductRow')->getRowHeader($attributeCodes));
        foreach ($products as $product) {
            if (!in_array($product->getId(), $exportedIds)) {
                $exportedIds[] = $product->getId();
                $row = Mage::helper('importexport/ProductRow')->productToRow($product, $attributeCodes, $includeImages);
                fputcsv($f, $row);

                if ($product->type_id == 'configurable') {
                    if ($product->getUsedProductCollection()) {
                        $simpleCollection = $product->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                        foreach ($simpleCollection as $childProduct) {
                            if (!in_array($childProduct->getId(), $exportedIds)) {
                                $exportedIds[] = $childProduct->getId();
                                $row = Mage::helper('importexport/ProductRow')->productToRow($childProduct, $attributeCodes, $includeImages);
                                fputcsv($f, $row);
                            }
                        }
                    }
                }
            }
        }
        fclose($f);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    }

    public function pagesAction()
    {
        header('Content-Type: application/json');

        $manufacturer = isset($_POST['manufacturer']) ? $_POST['manufacturer'] : null;
        $pageSize = 500;

        $attributeCodes = [];
        foreach ($_POST as $postKey => $postValue) {
            if (substr($postKey, 0, 4) == "att_") {
                $attributeCodes[] = preg_replace("/^att_/is", "", $postKey);
            }
        }

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', 4);
        if ($manufacturer) {
            $products
                ->addAttributeToFilter('manufacturer', array('eq' => $manufacturer));
        }

        $count = $products->getSize();

        echo json_encode([
            'result' => ceil($count / $pageSize),
            'status' => 'OK',
        ]);

        exit();
    }
}