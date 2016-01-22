<?php

class Hevelop_Gls_Model_Cron
{
    public function exportShipment()
    {
        Mage::getModel('hevelop_gls/exporter')->export();
        return;
    }
}
