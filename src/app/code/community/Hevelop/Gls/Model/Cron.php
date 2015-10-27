<?php

class Hevelop_Gls_Model_Cron
{
    const NEW_LINE = "\r\n";

    public $fp = false;

    public function exportShipment()
    {
        $helperGls = Mage::helper('hevelop_gls');
        if (!$helperGls->isEnabled()) {
            $this->debug('Hevelop Gls export not started because module is not enabled');
            return $this;
        }
        $helperGls->debug('Started Export Shipment Gls');

        $shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection()
            ->addFieldToFilter('exported_gls', 0);

        if ($shipmentCollection->getSize()) {
            $filename = substr(time(), 0, 8) . '.csv';
            $folder = Mage::getBaseDir('var') . DS . 'gls' . DS;
            $filenameComplete = $folder . $filename;

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $this->fp = fopen($filenameComplete, 'w');

            //using array walk to prevent memory leaks
            Mage::getSingleton('core/resource_iterator')->walk(
                $shipmentCollection->getSelect(),
                array(array($this, 'walkShipment'))
            );
            fclose($this->fp);
            $this->uploadFile($filenameComplete, $filename);
        }
        $helperGls->debug('Ended Export Shipment Gls');
    }

    public function walkShipment($args)
    {
        $helperGls = Mage::helper('hevelop_gls');
        $helperGls->debug('walking');
        $_shipment = Mage::getModel('sales/order_shipment');
        $_shipment->setData($args['row']);
        $row = $this->formatRow($_shipment);
        fwrite($this->fp, $row);

        $_shipment->setExportedGls(1);
        $_shipment->save();

        $helperGls->debug('row writed');
    }

    public function formatRow($shipment)
    {
        $helperGls = Mage::helper('hevelop_gls');
        $row = '';

        $shippingAddress = $shipment->getShippingAddress();

        //Ragine Sociale
        if ($shippingAddress->getCompany()) {
            $shippingName = $shippingAddress->getName() . ' c/o ' . $shippingAddress->getCompany();
        } else {
            $shippingName = $shippingAddress->getName();
        }
        $this->addColumn($row, $helperGls->formatString($shippingName, 35));

        //Indirizzo
        $shippingStreet = $shippingAddress->getStreet();
        $shippingStreet = is_array($shippingStreet) ? implode(' ', $shippingStreet) : $shippingStreet;
        $this->addColumn($row, $helperGls->formatString($shippingStreet, 35));

        //Città
        $this->addColumn($row, $helperGls->formatString($shippingAddress->getCity(), 30));

        //CAP
        $this->addColumn($row, $helperGls->formatNumber($shippingAddress->getPostcode(), 5));

        //Sigla Provincia
        $this->addColumn($row, $helperGls->formatString($shippingAddress->getRegion(), 2));

        //Numero documento di trasporto
        $this->addColumn($row, $helperGls->formatNumber($shipment->getIncrementId(), 10));

        //Data documento di trasporto
        $shipmentDate = date('ymd', strtotime($shipment->getCreatedAt()));
        $this->addColumn($row, $helperGls->formatString($shipmentDate, 6));

        //Numero colli
        $this->addColumn($row, $helperGls->formatNumber($shipment->getTotalQty(), 5));

        //Numero bancali
        $this->addColumn($row, $helperGls->formatNumber(0, 2));

        //Peso
        $this->addColumn($row, $helperGls->formatNumber($shipment->getTotalWeight(), 6, 1));

        //Importo Contrassegno
        $this->addColumn($row, $helperGls->formatNumber(0, 10, 2));

        //Note
        $this->addColumn($row, $helperGls->formatString('', 60));

        //Tipo di Porto
        //F=Franco - A=Assegnato
        $this->addColumn($row, $helperGls->formatString('F', 1));

        //Fermo Deposito
        $this->addColumn($row, $helperGls->formatString('', 15));

        //Assicurazione
        $this->addColumn($row, $helperGls->formatNumber(0, 11, 2));

        //Peso Volume
        $this->addColumn($row, $helperGls->formatNumber(0, 11, 1));

        //Riferimento Cliente
        $this->addColumn($row, $helperGls->formatString('', 600));

        //Note Aggiuntive Cliente
        $this->addColumn($row, $helperGls->formatString('', 40));

        //Id Collo Iniziale
        $this->addColumn($row, $helperGls->formatNumber(0, 15));

        //Id Collo Finale
        $this->addColumn($row, $helperGls->formatNumber(0, 15));

        //Notifica Email
        $this->addColumn($row, $helperGls->formatString('', 70));

        //Notifica Sms
        $this->addColumn($row, $helperGls->formatString('', 20));

        //Codici Servizi Sprinter
        $this->addColumn($row, $helperGls->formatString('', 17));

        //Filler
        $this->addColumn($row, $helperGls->formatString('', 33));

        //Data Prenotazione
        $this->addColumn($row, $helperGls->formatString('', 6));

        //Note Orario
        $this->addColumn($row, $helperGls->formatString('', 40));

        //Modalità Incasso
        //CONT=CONTANTE
        //AB=ASS BANCARIO
        //ABC=ASS BANC/CIRC NO PT
        //ABP=ASS BANCA / POSTA
        //AC=ASS CIRCOLARE
        //AP=ASS POSTALE
        //ARM=ASS COM RIL INT MITT
        //ARMP=COM RIL INT MI NO PT
        //ASR=ASS COME RILASCIATO
        //ASRP=ASS COM RIL NO PT
        //ASS=ASS CIRC/BANC/POST
        $this->addColumn($row, $helperGls->formatString('', 4));

        $this->addColumn($row, self::NEW_LINE);
        return $row;
    }

    public function addColumn(&$row, $column)
    {
        $row .= $column;
    }

    public function uploadFile($filenameComplete, $filename)
    {
        $helperGls = Mage::helper('hevelop_gls');
        $uploader = Mage::getModel('hevelop_gls/uploader');

        //Se è bloccato da un file di un'altra esportazione spedizioni, mi fermo. Altrimenti inserisco un BLOCCO
        if ($uploader->_hasLock(Mage::getBaseDir('var') . DS . 'gls' . DS, 'shipment')) {
//            $this->notify->addErrorMessage("EXP00", "Procedura locked! ");
            $helperGls->debug("Procedura locked! ");
            throw new Exception("Procedura locked!");
        }

        try {
            $helperGls->debug("Start upload file");

            //invia il file al server FTP
            if ($uploader->uploadToFtp($filenameComplete, $filename)) {
//                $this->notify->addSuccesMessage("EXP91", "Moved {$xmlFilename} to backup");
                $helperGls->debug("Upload {$filename} success, moving to backup");

                //sposta il file in backup
                $uploader->moveToBackup($filenameComplete);
                $helperGls->debug("Moved {$filename} to backup");
            } else {

            }

        } catch (Exception $e) {
//            $this->notify->addErrorMessage("EXP03", "Errore nell'esportazione di un ordine, procedura stoppata! " . $e->getMessage());
            $helperGls->debug("Errore nell'esportazione spedizioni, procedura stoppata! " . $e->getMessage());
            throw new Exception("Procedura stoppata!");
        }

        //Sblocco
        $uploader->_removeLock(Mage::getBaseDir('var') . DS . 'gls' . DS, 'shipment');

    }
}
