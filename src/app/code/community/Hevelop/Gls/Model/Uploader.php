<?php

/**
 * Uploader class to manage ftp uploads
 *
 * @author      Davide Barlssina <davide@hevelop.com>
 */
class Hevelop_Gls_Model_Uploader extends Mage_Core_Model_Abstract
{

    /**
     * Sposta il file nella cartella di backup, la crea se non esiste
     * @param string $backupDir
     * @return bool
     */
    public function moveToBackup($fullPath, $backupDir = 'backup')
    {
        $glsPath = Mage::getBaseDir('var') . DS . 'gls' . DS;
        if (!is_dir($glsPath . DS . $backupDir)) {
            if (!mkdir($glsPath . DS . $backupDir, 0777))
                return false;
        }

        $filename = str_replace($glsPath, '', $fullPath);
        $backupPath = $glsPath . DS . $backupDir . DS . $filename;

        return rename($fullPath, $backupPath);
    }

    /**
     * Upload di un file via FTP
     * @param string $path Percorso completo del file
     * @param string $filename Nome che il file assumerà nel server FTP
     * @return bool
     * @throws Exception In caso di connessione fallita
     */
    public function uploadToFtp($path, $filename)
    {
        $helperGls = Mage::helper('hevelop_gls');
        if (!$helperGls->isFtpEnabled()) {
//            $this->notify->addErrorMessage("FTP00", "FTP is DISABLED");
            $helperGls->debug("FTP is DISABLED");
            return false;
        }

        $ftpHelper = Mage::helper('hevelop_gls/ftp');

        //Connessione e login all'FTP
        $ftpHelper->getConnection($helperGls->getFtpHost());
        $ftpHelper->login($helperGls->getFtpUser(), $helperGls->getFtpPassword());

        // upload the file
        $remote_file = $helperGls->getFtpPath() . $filename;

        try {
            $ftpHelper->uploadFile($remote_file, $path);
//            $this->notify->addSuccesMessage("FTP90", "{$path} uploaded to {$remote_file}");
            $helperGls->debug("FTP90", "{$path} uploaded to {$remote_file}");
        } catch (Exception $e) {
//            $this->notify->addErrorMessage("FTP01", "Error uploading file {$file} to FTP!");
            $helperGls->debug("Error uploading file {$path} to FTP!");
        }

        $ftpHelper->close();
    }

    public function _hasLock($lock_folder, $string)
    {

//        if (file_exists($this->_lock_folder . $string . '.txt') && (time() - filemtime($this->_lock_folder . $string . '.txt')) > 3 * 60 * 60) {
//            $this->_removeLock($string);
//        }

        if ($lock_folder && !is_dir($lock_folder)) {
            mkdir($lock_folder, 0777, true);
        }

        if (file_exists($lock_folder . $string . '.txt')) {
            return true;
        }

        $lockHandle = fopen($lock_folder . $string . '.txt', 'w');
        fwrite($lockHandle, 'lock');
        fclose($lockHandle);

        return false;
    }

    public function _removeLock($lock_folder, $string)
    {
        if (file_exists($lock_folder . $string . '.txt')) {
            unlink($lock_folder . $string . '.txt');
        }
    }

    /**
     * Gestione dei fatal error
     */
    public function _setErrorHandlers()
    {
        register_shutdown_function(array($this, 'fatalErrorShutdownHandler'));
    }

    public function fatalErrorShutdownHandler()
    {
        $helperGls = Mage::helper('hevelop_gls');
        $last_error = error_get_last();
        if ($last_error['type'] === E_ERROR) {
            // fatal error
//            $this->notify->addErrorMessage(E_ERROR, $last_error['message'] . ' - ' . $last_error['file'] . ' - ' . $last_error['line']);
            $helperGls->debug($last_error['message'] . ' - ' . $last_error['file'] . ' - ' . $last_error['line']);
        }
    }
}
