<?php

class Capimichi_ImportExport_AjaxImportController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        echo json_encode([
            'ciao' => 'si'
        ]);
        die();
    }
}