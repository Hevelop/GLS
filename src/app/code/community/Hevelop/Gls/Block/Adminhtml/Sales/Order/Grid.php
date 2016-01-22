<?php
if (Mage::helper('hevelop_gls')->isModuleInstalled('Raveinfosys_Deleteorder', 'Raveinfosys_Deleteorder_Block_Adminhtml_Sales_Order_Grid')) {
    class Hevelop_Gls_Block_Adminhtml_Sales_Order_GridCommon extends Raveinfosys_Deleteorder_Block_Adminhtml_Sales_Order_Grid
    {
    }
} else {
    class Hevelop_Gls_Block_Adminhtml_Sales_Order_GridCommon extends Mage_Adminhtml_Block_Sales_Order_Grid
    {
    }
}

class Hevelop_Gls_Block_Adminhtml_Sales_Order_Grid extends Hevelop_Gls_Block_Adminhtml_Sales_Order_GridCommon
{
    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportGls', Mage::helper('hevelop_gls')->__('GLS CSV'));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     */
    public function getGlsFile()
    {
        $this->_isExport = true;
        $this->_prepareGrid();

        $io = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'gls' . DS;
        $name = substr(time(), 0, 8);
        $file = $path . DS . $name . '.csv';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWriteCsv(Mage::getModel('hevelop_gls/exporter')->getHeaderRow(), Mage::getStoreConfig('hevelopgls/general/delimiter'));

        //Write row with order information
        $this->_exportIterateCollection('_exportGlsItem', array($io));

        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type' => 'filename',
            'value' => $file,
            'rm' => true // can delete file after use
        );
    }

    /**
     * Write item data to csv export file
     *
     * @param Varien_Object $item
     * @param Varien_Io_File $adapter
     */
    protected function _exportGlsItem(Varien_Object $item, Varien_Io_File $adapter)
    {
        $row = Mage::getModel('hevelop_gls/exporter')->formatRow($item);
        $adapter->streamWriteCsv($row, Mage::getStoreConfig('hevelopgls/general/delimiter'));
    }
}