<?php

class Hevelop_Gls_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'hevelopgls/general/enabled';
    const XML_PATH_DEBUG_ENABLED = 'hevelopgls/general/debug_enabled';
    const XML_PATH_FTP_ENABLED = 'hevelopgls/ftp/enabled';
    const XML_PATH_FTP_HOST = 'hevelopgls/ftp/host';
    const XML_PATH_FTP_USER = 'hevelopgls/ftp/user';
    const XML_PATH_FTP_PASSWORD = 'hevelopgls/ftp/password';
    const XML_PATH_FTP_PATH = 'hevelopgls/ftp/remote_path';
    const LOG_FILE = 'export_gls.log';


    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    public function isFtpEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_FTP_ENABLED);
    }

    public function getFtpHost()
    {
        if ($this->isFtpEnabled()) {
            return Mage::getStoreConfig(self::XML_PATH_FTP_HOST);
        }
        return null;
    }

    public function getFtpUser()
    {
        if ($this->isFtpEnabled()) {
            return Mage::getStoreConfig(self::XML_PATH_FTP_USER);
        }
        return null;
    }

    public function getFtpPassword()
    {
        if ($this->isFtpEnabled()) {
            return Mage::getStoreConfig(self::XML_PATH_FTP_PASSWORD);
        }
        return null;
    }

    public function getFtpPath()
    {
        if ($this->isFtpEnabled()) {
            return Mage::getStoreConfig(self::XML_PATH_FTP_PATH);
        }
        return null;
    }

    public function isDebugEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEBUG_ENABLED);
    }

    public function formatString($string = '', $length)
    {
        if (is_null($string)) {
            $string = '';
        }
        $string = Mage::helper('transliteration')->trslt($string);
        $string = substr($string, 0, $length);
        $string = str_pad($string, $length, " ", STR_PAD_RIGHT);
        return $string;
    }

    public function formatNumber($number = 0, $length, $decimals = 0)
    {
        if (is_null($number)) {
            $number = 0;
        }
        $number = preg_replace('/[^0-9,\.]+/', '', $number);
        if ($decimals > 0) {
            $number = number_format($number, $decimals, ',', '');
        } else {
            $number = (int)floor($number);
        }
        $number = substr($number, -1 * $length);
        $number = str_pad($number, $length, "0", STR_PAD_LEFT);
        return $number;
    }


    public function debug($message)
    {
        if ($this->isDebugEnabled()) {
            Mage::log($message, Zend_Log::DEBUG, self::LOG_FILE);
        }
    }
}
