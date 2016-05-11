<?php

/**
 * André Felipe Silveira
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package    Silveira_Jadlog
 * @author     André Felipe Silveira (andrefelipesilveira@gmail.com.br)
 * @copyright  Copyright (c) 20015
 * @license    OSL 3.0
 */

class Silveira_Jadlog_Model_Carrier_Default extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

    private $mode;
    private $password;
    private $insurence;
    private $orderValue;
    private $collectionValue;
    private $zipTo;
    private $zipFrom;
    private $weight;
    private $payDestination;
    private $typeDelivery;
    private $cnpj;
    private $carrierTitle;
    private $request;
    private $_result;
    private $soap_url_wsdl = "http://www.jadlog.com.br:8080/JadlogEdiWs/services/ValorFreteBean?wsdl";
    private $method = "valorar";

    private function getClient(){
        try {
            $soapClient = new SoapClient($this->soap_url_wsdl, array('cache_wsdl' => WSDL_CACHE_NONE, 'encoding' => 'UTF-8','soap_version' => SOAP_1_1));
            return $soapClient;

        } catch (Exception $e) {
            Mage::log("Servidor está offline - ". $e->getMessage() ,null,  'jadlog_errors.log');
            return false;
        }
    }


    public function collectRates(Mage_Shipping_Model_Rate_Request $request){
        if(!$this->getConfigData('active')){
            return false;
        }

        $this->request = $request;
        $this->_result = Mage::getModel('shipping/rate_result');

        if(!$this->initializeParams()) {
            return $this->_result;
        }

        $this->calcShippingPrice();

        return $this->_result;
    }

    private function initializeParams(){
        $quote = $this->getQuote();
        $quoteData = $quote->getData();

        if($this->getConfigData('weight') != 'k'){
            $this->weight = $this->request->getPackageWeight() / 1000;
        }else{
            $this->weight = $this->request->getPackageWeight();
        }

        $this->zipTo = $this->request->getDestPostcode();
        $this->zipTo = str_replace(array('-', '.'), '', trim($this->zipTo));
        if (!preg_match('/^([0-9]{8})$/', $this->zipTo)) {
            Mage::log('JadLog: To ZIP Code Error');
            return false;
        }

        $this->orderValue = $quoteData['grand_total'] == null ? 0 : $quoteData['grand_total'];
        $this->mode = $this->getConfigData('mode');
        $this->password = $this->getConfigData('password');
        $this->insurence = $this->getConfigData('insurance');
        $this->collectionValue = $this->getConfigData('collection_value');
        $this->zipFrom = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());
        $this->zipFrom = str_replace(array('-', '.'), '', trim($this->zipFrom));
        if (!preg_match('/^([0-9]{8})$/', $this->zipFrom)) {
            Mage::log('JadLog: From ZIP Code Error - '.$this->zipFrom);
            return false;
        }

        $this->payDestination = $this->getConfigData('pay_destination');
        $this->typeDelivery = $this->getConfigData('type_delivery');
        $this->cnpj = $this->getConfigData('cnpj');
        $this->carrierTitle = $this->getConfigData('carrier_title');

        return true;
    }

    private function calcShippingPrice(){
        $client = $this->getClient();
        $options = array('location' => $this->soap_url_wsdl);

        foreach (explode(',',$this->mode) as $key => $mode) {

            $arguments = array('valorar' => array("vModalidade" => $mode,
                "Password" => $this->password,
                "vSeguro" => $this->insurence,
                "vVlDec" => $this->orderValue,
                "vVlColeta" => $this->collectionValue,
                "vCepOrig" => $this->zipFrom,
                "vCepDest" => $this->zipTo,
                "vPeso" => $this->weight,
                "vFrap" => $this->payDestination,
                "vEntrega" => $this->typeDelivery,
                "vCnpj" => $this->cnpj)
            );

            try{
                $result = $client->__soapCall($this->method, $arguments, $options);
            }catch (Exception $e){
                Mage::log("Erro ao consultar Jadlog API -". $e->getMessage() , null, 'silveira_jadlog_errors.log');
                return false;
            }
            $simplexml = new \SimpleXMLElement($result->valorarReturn);
            $value = $simplexml->Jadlog_Valor_Frete->Retorno;

            if ($value == '-1') {
                Mage::log("Acesso Negado - Dados Incorretos -". $simplexml->Mensagem , null, 'silveira_jadlog_errors.log');
                continue;
            }else if ($value == '-2'){
                Mage::log("Parametros Incorretos - ". $simplexml->Mensagem , null, 'silveira_jadlog_errors.log');
                continue;
            }else if($value == '-3'){
                Mage::log("Erro ao ler banco de dados - ". $simplexml->Mensagem , null, 'silveira_jadlog_errors.log');
                continue;
            }

            $number = doubleval(str_replace(",", ".", str_replace(".", "", $value)));

            $price = (float)str_replace(',', '.', $number);

             if($price > (float) 0){
                $method = Mage::getModel('shipping/rate_result_method');
                $method->setCarrier('jadlog');
                $method->setCarrierTitle($this->carrierTitle);
                $method->setMethod('jadlog_'.$key);
                $method->setMethodTitle($this->getMethodTitle($mode));
                $method->setPrice($price);
                $this->_result->append($method);
            }

        }
    }

    private function getMethodTitle($mode){
        switch ($mode) {
            case '0':
                return 'EXPRESSO - Aéreo';
                break;
            case '3':
                return 'PACKAGE - Rodoviário';
                break;
            case '4':
                return 'RODOVIÁRIO';
                break;
            case '5':
                return 'ECONÔMICO - Rodoviário';
                break;
            case '6':
                return 'DOC - Rodoviário';
                break;
            case '7':
                return 'CORPORATE - Aéreo';
                break;
            case '9':
                return 'COM - Aéreo';
                break;
            case '10':
                return 'INTERNACIONAL - Aéreo';
                break;
            case '12':
                return 'CARGO - Aéreo';
                break;
            default:
                return 'EMERGENCIAL - Rodoviário';
                break;
        }
    }


    private function getSession(){
        return Mage::getSingleton('checkout/session');
    }

    private function getQuote(){
        return $this->getSession()->getQuote();
    }

    private function getCart(){
        return Mage::getSingleton('checkout/cart');
    }

    // override
    public function getAllowedMethods() {
        return array($this->_code => $this->getConfigData('title'));
    }

    public function getConfigData($field){
        return Mage::getStoreConfig('carriers/jadlog/'.$field);
    }
}
