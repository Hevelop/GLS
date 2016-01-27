<?php

class Hevelop_Gls_Model_Admin_Source_Codpayment
{

    const EVALUATION_POSITIVE = 1;
    const EVALUATION_NEGATIVE = 0;

    /**
     * Returns options for select evaluation value
     * @return array
     */
    public function toOptionArray()
    {

        return $this->getActivPaymentMethods();
    }

    public function getActivPaymentMethods()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();

        $methods = array(array('value' => '', 'label' => Mage::helper('adminhtml')->__('--Please Select--')));

        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode,
            );
        }

        return $methods;

    }

}