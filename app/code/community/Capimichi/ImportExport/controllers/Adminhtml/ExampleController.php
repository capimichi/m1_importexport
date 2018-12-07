<?php

class Capimichi_ImportExport_Adminhtml_ExampleController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        // "Fetch" display
        $this->loadLayout();
        
        // "Inject" into display
        // THe below example will not actualy show anything since the core/template is empty
        $this->_addContent($this->getLayout()->createBlock('core/template'));

        // echo "Hello developer...";

        // "Output" display
        $this->renderLayout();
    }
}