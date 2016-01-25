<?php

class Hevelop_Gls_Model_Exporter
{
    const NEW_LINE = "\r\n";

    public $fp = false;

    public function export()
    {
        $helperGls = Mage::helper('hevelop_gls');
        if (!$helperGls->isEnabled()) {
            $this->debug('Hevelop Gls export not started because module is not enabled');
            return $this;
        }
        $helperGls->debug('Started Export Shipment Gls');

        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('exported_gls', 0);

        if ($orderCollection->getSize()) {
            $filename = substr(time(), 0, 8) . '.csv';
            $folder = Mage::getBaseDir('var') . DS . 'gls' . DS;
            $filenameComplete = $folder . $filename;

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $this->fp = fopen($filenameComplete, 'w');

            //using array walk to prevent memory leaks
            Mage::getSingleton('core/resource_iterator')->walk(
                $orderCollection->getSelect(), //query
                array('walkShipment'), //callback
                array() //args
            );
            fclose($this->fp);
            $this->uploadFile($filenameComplete, $filename);
        }
        $helperGls->debug('Ended Export Shipment Gls');
        return;
    }

    public function walkShipment($args)
    {
        $helperGls = Mage::helper('hevelop_gls');
        $helperGls->debug('walking');
        $_order = Mage::getModel('sales/order');
        $_order->setData($args['row']);
        $row = $this->formatRow($_order);
        fwrite($this->fp, implode(",", $row));

        $_order->setExportedGls(1);
        $_order->save();

        $helperGls->debug('row writed');
    }

    public function getHeaderRow()
    {
        $helperGls = Mage::helper('hevelop_gls');
        $row = array();

        //1 Ragine Sociale
        $this->addColumn($row, 'Ragion Sociale');
        //2 Indirizzo
        $this->addColumn($row, 'Indirizzo');
        //3 Località
        $this->addColumn($row, 'Località');
        //4 ZIP Code
        $this->addColumn($row, 'ZIP Code');
        //5 Provincia / Stato
        $this->addColumn($row, 'PR');
        //6 BDA / DDT
        $this->addColumn($row, 'BDA/DDT');
        //7 Data documento di trasporto
        $this->addColumn($row, 'Data documento di trasporto');
        //8 Colli
        $this->addColumn($row, 'Colli');
        //9 Incoterm
        $this->addColumn($row, 'Incoterm');
        //10 Peso reale
        $this->addColumn($row, 'Peso reale');
        //11 Importo Contrassegno
        $this->addColumn($row, 'Importo Contrassegno');
        //12 Note
        $this->addColumn($row, 'Note');
        //13 Tipo porto
        $this->addColumn($row, 'Tipo porto');
        //14 Colonna vuota
        $this->addColumn($row, 'Colonna vuota');
        //15 Importo assicurazione
        $this->addColumn($row, 'Importo assicurazione');
        //16 Peso Volume
        $this->addColumn($row, 'Peso Volume');
        //17 Tipo di Collo
        $this->addColumn($row, 'Tipo di Collo');
        //18 Colonna vuota
        $this->addColumn($row, 'Colonna vuota');
        //19 Riferimenti etichettatura
        $this->addColumn($row, 'Riferimenti etichettatura');
        //20 Note Aggiuntive Cliente
        $this->addColumn($row, 'Note Aggiuntive Cliente');
        //21 Codice Cliente
        $this->addColumn($row, 'Codice Cliente');
        //22 Valore Dichiarato
        $this->addColumn($row, 'Valore Dichiarato');
        //23 Id Collo Iniziale
        $this->addColumn($row, 'Id Collo Iniziale');
        //24 Id Collo Finale
        $this->addColumn($row, 'Id Collo Finale');
        //25 Notifica Email
        $this->addColumn($row, 'Notifica Email');
        //26 Notifica Sms 1
        $this->addColumn($row, 'Notifica Sms 1');
        //27 Notifica Sms 2
        $this->addColumn($row, 'Notifica Sms 2');
        //28 Servizi accessori
        $this->addColumn($row, 'Servizi accessori');
        //29 Modalità Incasso
        $this->addColumn($row, 'Modalità Incasso');
        //30 Data Prenotazione  (GDO)
        $this->addColumn($row, 'Data Prenotazione (GDO)');
        //31 Note e/o Orario (GDO)
        $this->addColumn($row, 'Note Orario');
        //32 Notifica Sms (Parcel)
        $this->addColumn($row, 'Notifica Sms (Parcel)');
        //33 IdentPIN
        $this->addColumn($row, 'IdentPIN');
        //34 Assicurazione integrativa
        $this->addColumn($row, 'Assicurazione integrativa');
        //35 Persona riferimento
        $this->addColumn($row, 'Persona riferimento');
        //36 Telefono destinatario
        $this->addColumn($row, 'Telefono destinatario');
        //37 Categoria merceologica
        $this->addColumn($row, 'Categoria merceologica');
        //38 Fattura doganale
        $this->addColumn($row, 'Fattura doganale');
        //39 Data fattura doganale
        $this->addColumn($row, 'Data fattura doganale');
        //40 Pezzi dichiarati
        $this->addColumn($row, 'Pezzi dichiarati');
        //41 Nazione d'origine
        $this->addColumn($row, "Nazione d'origine");
        //42 Telefono mittente
        $this->addColumn($row, 'Telefono mittente');

        return $row;
    }

    public function formatRow($order)
    {
        $helperGls = Mage::helper('hevelop_gls');
        $row = array();

        $shippingAddress = $order->getShippingAddress();

        //1 Ragine Sociale
        if ($shippingAddress->getCompany()) {
            $shippingName = $shippingAddress->getName() . ' c/o ' . $shippingAddress->getCompany();
        } else {
            $shippingName = $shippingAddress->getName();
        }
        $this->addColumn($row, $helperGls->formatString($shippingName, 35));

        //2 Indirizzo
        $shippingStreet = $shippingAddress->getStreet();
        $shippingStreet = is_array($shippingStreet) ? implode(' ', $shippingStreet) : $shippingStreet;
        $this->addColumn($row, $helperGls->formatString($shippingStreet, 35));

        //3 Località
        $this->addColumn($row, $helperGls->formatString($shippingAddress->getCity(), 30));

        //4 ZIP Code
        $this->addColumn($row, $helperGls->formatNumber($shippingAddress->getPostcode(), 5));

        //5 Provincia / Stato
        $this->addColumn($row, $helperGls->formatString($shippingAddress->getRegionCode(), 2));

        //6 BDA / DDT
        $this->addColumn($row, $helperGls->formatNumber($order->getIncrementId(), 10));

        //7 Data documento di trasporto
        $orderDate = date('ymd', time()); //Data shipment uguale al momento dell'export
        $this->addColumn($row, $helperGls->formatString($orderDate, 6));

        //8 Colli
        $this->addColumn($row, $helperGls->formatNumber(1, 5));

        //9 Incoterm
        $this->addColumn($row, $helperGls->formatNumber(0, 2));

        //10 Peso reale
        $totalWeight = 0;
        //ciclo gli items e calcolo i weight
        $orderItems = $order->getAllItems();
        foreach ($orderItems AS $item) {
            $itemData = $item->getData();
            $weight = 0;
            if (isset($itemData['weight'])) {
                $weight = $item->getWeight();
            }
            $totalWeight += $weight * $item->getQty();
        }
        if ($totalWeight == 0) {
            $totalWeight = 1;
        }
        $this->addColumn($row, $helperGls->formatNumber($totalWeight, 6, 1));

        //11 Importo Contrassegno
        $this->addColumn($row, $helperGls->formatNumber(0, 10, 2));

        //12 Note
        $this->addColumn($row, $helperGls->formatString('', 40));

        //13 Tipo porto
        //F=Franco - A=Assegnato
        $this->addColumn($row, $helperGls->formatString('F', 1));

        //14 Colonna vuota
        $this->addColumn($row, $helperGls->formatString(''));

        //15 Importo assicurazione
        $this->addColumn($row, $helperGls->formatNumber(0, 11, 2));

        //16 Peso Volume
        $this->addColumn($row, $helperGls->formatNumber(0, 11, 1));

        //17 Tipo di Collo
        $this->addColumn($row, $helperGls->formatNumber(0, 1));

        //18 Colonna vuota
        $this->addColumn($row, $helperGls->formatString(''));

        //19 Riferimenti etichettatura
        $this->addColumn($row, $helperGls->formatString('', 600));

        //20 Note Aggiuntive Cliente
        $this->addColumn($row, $helperGls->formatString('', 40));

        //21 Codice Cliente
        $this->addColumn($row, $helperGls->formatString('', 30));

        //22 Valore Dichiarato
        $this->addColumn($row, $helperGls->formatString('', 11));

        //23 Id Collo Iniziale
        $this->addColumn($row, $helperGls->formatNumber(0, 15));

        //24 Id Collo Finale
        $this->addColumn($row, $helperGls->formatNumber(0, 15));

        //25 Notifica Email
        $this->addColumn($row, $helperGls->formatString('', 70));

        //26 Notifica Sms 1
        $this->addColumn($row, $helperGls->formatNumber(0, 10));

        //27 Notifica Sms 2
        $this->addColumn($row, $helperGls->formatNumber(0, 10));

        //28 Servizi accessori
        $this->addColumn($row, $helperGls->formatString('', 50));

        //29 Modalità Incasso
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

        //30 Data Prenotazione
        $this->addColumn($row, $helperGls->formatString('', 6));

        //31 Note e/o Orario
        $this->addColumn($row, $helperGls->formatString('', 40));
        //32 Notifica Sms (Parcel)
        $this->addColumn($row, $helperGls->formatNumber(0, 20));
        //33 IdentPIN
        $this->addColumn($row, $helperGls->formatString('', 15));
        //34 Assicurazione integrativa
        $this->addColumn($row, $helperGls->formatString('', 1));
        //35 Persona riferimento
        $this->addColumn($row, $helperGls->formatString('', 50));
        //36 Telefono destinatario
        $this->addColumn($row, $helperGls->formatString('', 15));
        //37 Categoria merceologica
        $this->addColumn($row, $helperGls->formatString('', 6));
        //38 Fattura doganale
        $this->addColumn($row, $helperGls->formatString('', 20));
        //39 Data fattura doganale
        $this->addColumn($row, $helperGls->formatString('', 6));
        //40 Pezzi dichiarati
        $this->addColumn($row, $helperGls->formatString('', 6));
        //41 Nazione d'origine
        $this->addColumn($row, $helperGls->formatString('', 3));
        //42 Telefono mittente
        $this->addColumn($row, $helperGls->formatString('', 16));

        return $row;
    }

    public function addColumn(&$row, $column)
    {
        $row [] = $column;
    }

    public function uploadFile($filenameComplete, $filename)
    {
        $helperGls = Mage::helper('hevelop_gls');
        $uploader = Mage::getModel('hevelop_gls/uploader');

        //Se è bloccato da un file di un'altra esportazione spedizioni, mi fermo. Altrimenti inserisco un BLOCCO
        if ($uploader->_hasLock(Mage::getBaseDir('var') . DS . 'gls' . DS, 'shipment')) {
            $helperGls->debug("Procedura locked! ");
            throw new Exception("Procedura locked!");
        }

        try {
            $helperGls->debug("Start upload file");

            //invia il file al server FTP
            if ($uploader->uploadToFtp($filenameComplete, $filename)) {
                $helperGls->debug("Upload {$filename} success, moving to backup");

                //sposta il file in backup
                $uploader->moveToBackup($filenameComplete);
                $helperGls->debug("Moved {$filename} to backup");
            } else {

            }

        } catch (Exception $e) {
            $helperGls->debug("Errore nell'esportazione spedizioni, procedura stoppata! " . $e->getMessage());
            throw new Exception("Procedura stoppata!");
        }

        //Sblocco
        $uploader->_removeLock(Mage::getBaseDir('var') . DS . 'gls' . DS, 'shipment');

    }
}
