<?php
require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'OrderController.php');

class Hevelop_Gls_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{

    /**
     * Export order grid to CSV format
     */
    public function exportGlsAction()
    {
        $fileName = 'gls_export' . date('ymd', time()) . '.csv';
        $grid = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getGlsFile());
    }

}
