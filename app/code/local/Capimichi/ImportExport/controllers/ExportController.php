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

        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . implode("-", [
                'cm-export',
                strtotime('now'),
                '.csv',
            ]);
        $f = fopen($filePath, "w");

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*');

        if ($manufacturer) {
            $products
                ->addAttributeToFilter('manufacturer', array('eq' => $manufacturer));
        }

        fputcsv($f, Mage::helper('importexport/Csv')->getRowHeader());
        foreach ($products as $product) {
            $row = Mage::helper('importexport/Csv')->simpleProductToRow($product);
            fputcsv($f, $row);
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
}