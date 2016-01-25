<?php

class Hevelop_Gls_Model_Carrier_Gls extends Mage_Shipping_Model_Carrier_Flatrate
{
    protected $_code = 'hevelop_gls';

    public function isTrackingAvailable()
    {
        return true;
    }


    public function getTrackingInfo($tracking)
    {
        $info = array();

        $result = $this->getTracking($tracking);

        if($result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        }
        elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get tracking
     *
     * @param mixed $trackings
     * @return mixed
     */
    public function getTracking($trackings)
    {
        $return = array();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }


        $result = Mage::getModel('shipping/tracking_result');
        foreach ($trackings as $tracking) {
            $status = Mage::getModel('shipping/tracking_result_status');
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("https://www.gls-italy.com/?option=com_gls&view=track_e_trace&mode=search&numero_spedizione=$tracking&tipo_codice=nazionale");
            $result->append($status);
        }

        return $result;
    }

}
