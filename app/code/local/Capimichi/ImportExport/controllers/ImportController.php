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
        ];

        if (isset($_FILES['file'])) {
            $filePath = $_FILES['file']['tmp_name'];
            $rows = [];
            foreach (Mage::helper('importexport/Csv')->getRows($filePath) as $row) {

                $product = Mage::helper('importexport/ProductRow')->rowToSimpleProduct($row);
                $product->save();

                $stockItem = Mage::helper('importexport/StockRow')->rowToStock($product, $row);
                $stockItem->save();

                $rows[] = $product->getId();
            }
            $response['products'] = $rows;
        } else {
            $response['MISSING FILE'];
        }

        echo json_encode($response);
    }

}