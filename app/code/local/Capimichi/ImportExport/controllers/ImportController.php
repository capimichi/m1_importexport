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

        for ($i = 0; $i < 100; $i++) {

        }

        echo json_encode($_FILES);
    }

}