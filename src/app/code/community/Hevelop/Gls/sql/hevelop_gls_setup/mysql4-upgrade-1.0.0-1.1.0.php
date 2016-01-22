<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->dropColumn($installer->getTable('sales/shipment'), 'exported_gls');

$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'exported_gls', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length' => 1,
    'nullable' => false,
    'default' => 0,
    'comment' => 'Exported to GLS carrier'
));


$installer->endSetup();
