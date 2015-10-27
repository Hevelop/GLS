<?php

class Hevelop_Gls_Helper_Ftp extends Mage_Core_Helper_Abstract
{

    private $connection;

    public function getConnection($host = null)
    {
        if (!$this->connection) {
            if (!$host) throw new Exception('Missing HOST');

            $this->connection = ftp_connect($host);
            if (!$this->connection) {
                throw new Exception('FTP connection failed');
            }

        }
        return $this->connection;
    }


    public function login($user = null, $pass = null)
    {

        $connection = $this->getConnection();

        $login_result = ftp_login($connection, $user, $pass);
        if (!$login_result) {
            throw new Exception('FTP login failed');
        }

        //Modalità passiva attivata
        ftp_pasv($connection, true);
    }

    /**
     *
     * @param type $connection Connection
     * @param string $dir Directory to list
     * @return type List of file names
     */
    public function listDir($dir)
    {

        $connection = $this->getConnection();

        $list = ftp_nlist($connection, '.' . DS . $dir);

        return $list;
    }

    /**
     * Scarica un file dall'FTP
     * @param type $connection
     * @param string $local_file
     * @param string $server_file
     * @throws Exception
     */
    public function downloadFile($local_file, $server_file)
    {

        $connection = $this->getConnection();

        //TODO: switch per estensioni  FTP_ASCII/FTP_BINARY
        switch (true) {
            default:
                $mode = FTP_ASCII; //FTP_BINARY se non è un file di testo
        }

        $res = ftp_get($connection, $local_file, $server_file, $mode);

        if (!$res) {
            throw new Exception("FTP HELPER: download failed {$server_file} > {$local_file}");
        } else {
            Mage::helper('hevelop_gls')->debug("FTP HELPER: Downloaded {$server_file} > {$local_file}");
        }
    }

    public function uploadFile($remote_file, $filepath)
    {
        $connection = $this->getConnection();

        //TODO: switch per estensioni  FTP_ASCII/FTP_BINARY
        switch (true) {
            default:
                $mode = FTP_ASCII; //FTP_BINARY se non è un file di testo
        }

        Mage::helper('hevelop_gls')->debug("Upload del file {$remote_file}");
        $res = ftp_put($connection, $remote_file, $filepath, $mode); //FTP_ASCII per i file di testo in generale

        if (!$res) {
            throw new Exception("FTP HELPER: upload failed {$filepath} > {$remote_file}");
        } else {
            Mage::helper('hevelop_gls')->debug("FTP HELPER: Uploaded {$remote_file} > {$filepath}");
        }
    }

    /**
     *
     * @param string $serverFilename
     * @return bool
     */
    public function deleteFile($serverFilename)
    {

        $connection = $this->getConnection();

        return ftp_delete($connection, $serverFilename);
    }


    public function close()
    {
        $connection = $this->getConnection();

        return ftp_close($connection);
    }
}
