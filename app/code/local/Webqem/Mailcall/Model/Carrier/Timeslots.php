<?php
/**
 * Webqem Timeslots
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Carrier_Timeslots extends Mage_Shipping_Model_Carrier_Abstract 
	implements Mage_Shipping_Model_Carrier_Interface{
	
    /**
     * unique internal shipping method identifier
     *
     * @var string [a-z0-9_]
     */
    const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_GENERAL    = 'trans_email/ident_general/';
	const MAILCALL_FREE_SHIPPING_PROMO = 3;
    
    protected $_filePath = '/var/mailcall/';
    protected $_fileName = 'pricelist.xml';
    protected $_updatePriceDays = 7;
    protected $_code = 'timeslot';
    protected $_request = null;
    protected $_result = null;
    protected $_errors = array();
    protected $_mailcallRates = array();
    protected $_defaultGatewayUrl = 'http://api.mailcall.com.au/test.php';
    protected $_mailcallGatewayUrl = 'http://api.mailcall.com.au';
    protected $_retryTimes=5;
    protected $_errorReportEmail='it@mailcall.com.au';
    protected $_order;
    
    /**
     * Get block pickup to shipping method
     */
    public function getFormBlock()
    {
    	return 'webqemmailcall/timeslots_pickup';
    } 
    
    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
		
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $this->setRequest($request);
        
        if($this->isSpecificProduct()){
            return false;
        }
        
        $this->_result = $this->_getQuotes();
		
        $this->_updateFreeMethodQuote($request);
		
        return $this->getResult();
    }
    
    function isSpecificProduct(){
        $allowedProduct=$this->getConfigData('allowed_products');
        if($allowedProduct){
            $pass=false;
            foreach ($this->_request->getAllItems() as $_item) {
               $_product = Mage::getModel('catalog/product')->load($_item->getProduct()->getId());
               if(!$_product->getWantitnow()){
                   $pass=true;
                   break;
               }
            }
            return $pass;
        }else{
            return false;
        }
        
    }

    public function setRequest(Mage_Shipping_Model_Rate_Request $request) {
        $this->_request = $request;

        $r = new Varien_Object();

        $r->setStoreId($request->getStoreId());
		$r->setFreeShipping($request->getFreeShipping());
		$r->setMailcallFreeShipping($request->getFreeShipping());

        
        if ($request->getWebqemMailcallApikeys()) {
            $apikeys = $request->getWebqemMailcallApikeys();
        } else {
            $apikeys = $this->getConfigData('apikeys');
        }
        $r->setApikeys($apikeys);
        
        if ($request->getWebqemMailcallUsefixedcost()) {
            $usefixedcost = $request->getWebqemMailcallSetfixedcost();
        } else {
            $usefixedcost = $this->getConfigData('usefixedcost');
        }
        $r->setUsefixedcost($usefixedcost);
        
        if ($request->getWebqemMailcallDisplayWantitnow()) {
            $displayWantitnow = $request->getWebqemMailcallDisplayWantitnow();
        } else {
            $displayWantitnow = $this->getConfigData('display_wantitnow');
        }
        $r->setDisplayWangitnow($displayWantitnow);
        
        if ($request->getWebqemMailcallWithinkms()) {
            $withinkms = $request->getWebqemMailcallWithinkms();
        } else {
            $withinkms = $this->getConfigData('withinkms');
        }
        $r->setWithinkms($withinkms);
        
        
        if ($request->getWebqemMailcallFixedcost()) {
            $fixedcost = $request->getWebqemMailcallFixedcost();
        } else {
            $fixedcost = $this->getConfigData('fixedcost');
        }
        $r->setFixedcost($fixedcost);


        if ($request->getWebqemMailcallShipmentType()) {
            $shipmentType = $request->getWebqemMailcallShipmentType();
        } else {
            $shipmentType = $this->getConfigData('shipment_type');
        }
        $r->setShipmentType($shipmentType);

        if ($request->getWebqemMailcallDutiable()) {
            $shipmentDutible = $request->getWebqemMailcallDutiable();
        } else {
            $shipmentDutible = $this->getConfigData('dutiable');
        }
        $r->setDutiable($shipmentDutible);

        if ($request->getWebqemMailcallDutyPaymentType()) {
            $dutypaytype = $request->getWebqemMailcallDutyPaymentType();
        } else {
            $dutypaytype = $this->getConfigData('dutypaymenttype');
        }
        $r->setDutyPaymentType($dutypaytype);

        if ($request->getWebqemMailcallDisplayLogos()) {
            $display_logos = $request->getWebqemMailcallDisplayLogos();
        } else {
            $display_logos = $this->getConfigData('display_logos');
        }
        $r->setDisplayLogos($display_logos);

//        if ($request->getWebqemMailcallLocation()) {
//            $location = $request->getWebqemMailcallLocation();
//        } else {
//            $location = $this->getConfigData('location');
//        }
//        $r->setLocation($location);
        
        if ($request->getWebqemMailcallContentDesc()) {
            $contentdesc = $request->getWebqemMailcallContentDesc();
        } else {
            $contentdesc = $this->getConfigData('contentdesc');
        }
        $r->setContentDesc($contentdesc);

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        $origCountry = '';
        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
        }
        $r->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code());

        if ($request->getOrigSuburb()) {
            $r->setOrigSuburb($request->getOrigSuburb());
        } else {
            $r->setOrigSuburb(Mage::getStoreConfig('shipping/origin/region_id', $this->getStore()));
        }

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
        }

        if ($request->getOrigCity()) {
            $r->setOrigCity($request->getOrigCity());
        } else {
            $r->setOrigCity(Mage::getStoreConfig('shipping/origin/city', $this->getStore()));
        }

        /*
         * Mailcall only accepts weight as a whole number. Maximum length is 3 digits.
         */
        $r->setAllQty($request->getPackageQty());
        
        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $shippingWeight = round(max(1, $weight), 0);

        $r->setValue(round($request->getPackageValue(), 2));
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());
        $r->setDestStreet(Mage::helper('core/string')->substr($request->getDestStreet(), 0, 35));
        $r->setDestCity($request->getDestCity());

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = 'AU';
        }

//        if ($request->getInsuredCurrency()) {
//            $r->setInsuredCurrency($request->getInsuredCurrency());
//        } else {
//            $r->setInsuredCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());
//        }
//        if ($request->getInsuredValue()) {
//            $r->setInsuredValue($request->getInsuredValue());
//        } else {
//            $r->setInsuredValue($this->getConfigData('insured_type'));
//        }
        if ($request->getDestStreet()) {
            $r->setDestStreet($request->getDestStreet());
        } else {
            $r->setDestStreet($this->getConfigData('dest_street'));
        }

        $r->setDestCountryId($destCountry);
        $r->setDestState($request->getDestRegionCode());

        $r->setWeight($shippingWeight);
        $r->setFreeMethodWeight($request->getFreeMethodWeight());
        

        if ($request->getWebqemMailcallDayandhours()) {
            $dayandhours = $request->getWebqemMailcallDayandhours();
        } else {
            $dayandhours = $this->getConfigData('dayandhours');
        }
        $r->setServiceTime($dayandhours);


        $this->_rawRequest = $r;

        return $this;
    }
    
    public function setOrder($order){
        $this->_order=$order;
        
        return $this;
    }
    
    protected function getOrder(){
        return $this->_order;
    }

    public function getResult() {
        return $this->_result;
    }
    public function getPlaceOrderNoticeEmail(){
        return $this->_placeOrderNoticeEmail;
    }

    protected function _getQuotes() {
        return $this->_getXmlQuotes();
    }
    /*
     * send  email
     */
    public function _sendMailcallEmail($subject,$body,$toemail,$senderEMAIL,$senderNAME='default system from entry'){
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        
        $debugData = array('subject' => $subject,'body'=>$body,'senderEMAIL'=>$senderEMAIL,'senderNAME'=>$senderNAME,'toemail'=>$toemail);
        $this->_debug($debugData);
        
        try {
            /** add notify message**/
            $mail = new Zend_Mail();

            $mail->setBodyHtml($body);
            $mail->setFrom($senderEMAIL, $senderNAME);
            $mail->addTo($toemail);
            $mail->setSubject($subject);
            $mail->send();
            /***end add notify**/

            $translate->setTranslateInline(true);
        } catch (Exception $e) {
            $this->_debug(Mage::helper('contacts')->__('Unable to submit your send email request. Please, try again later.Error:'.$e->getMessage()));
            $translate->setTranslateInline(true);
        }
    }
    
	/*
     * send new order email to customer
     */
    //protected function sendNewOrderEmailToCustomer($privatelink,$wintracklink,$linenumber,$mobileauthcode){
    //modified by Mike @ Mailcall 30/06/2012
    protected function sendNewOrderEmailToCustomer($privatelink,$wintracklink,$linenumber,$mobileauthcode){
  	$order=$this->getOrder();
        $orderId=$order->getData('increment_id');
	$senderEMAIL='no-reply@wantitnow.com.au';
        $toemail=$order->getCustomerEmail();
		
	if ($order->getCustomerIsGuest()) {
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $customerName = $order->getCustomerName();
        }
		
        $subject='Want it Now Delivery Tracking for Order # '.$orderId;
        $body='Dear '.$customerName.'<br /><br />' ;
		$body.='Thank you for choosing Want it Now powered by Mail Call Couriers as your delivery method. <br /><br />';
                //Modified by Mike @ Mailcall 01/06/2012
		//$body.='Your order will be picked up shortly, for delivery today. We know this order is urgent so you can follow its real time status using this link <a href="'.$privatelink.'" target="_blank">'.$privatelink.'</a>.<br /><br />';
                $body.='Your order will be picked up shortly, for delivery today. We know this order is urgent so you can follow its real time status using this link: <a href="'.$wintracklink.'" target="_blank">Track your Want it Now delivery</a>.<br /><br />';
                $body.='Alternatively, you can download our <a href="http://www.wantitnow.com.au/download-our-app" target="_blank">mobile app for iPhone or Android handsets here</a> and track your package in real-time on your mobile.<br /><br />';
                $body.='Your Line Number for tracking the delivery is: <b>'.$linenumber.'</b><br />';
                $body.='Your Password for tracking the delivery is: <b>'.$mobileauthcode.'</b><br/><br />';
		$body.='If you have any questions regarding the delivery of your parcel, please contact customer service on 136 331. <br /><br />';
		$body.='Regards <br /><br />';
		$body.='Want it Now ';    
		
		
        $this->_sendMailcallEmail($subject, $body, $toemail, $senderEMAIL,'Want it Now');
        
        return;
    }
	/*
     * send new order email notice
     */
    protected function sendNewOrderNoticeEmail(){
  
        $order=$this->getOrder();
        $orderId=$order->getData('increment_id');
        $storeName=$this->getConfigData('storename');
        $senderEMAIL=Mage::getStoreConfig(self::XML_PATH_EMAIL_GENERAL.'email');
        
        $notificationEmail=$this->getConfigData('notification');
        if(!empty($notificationEmail)){
            $emailArr=explode(',',$notificationEmail);

            $subject='Want it Now Priority Order #'.$orderId;
            $body='Priority Order Notification<br />Order '.$orderId.' is using Want it as the shipping method. Please attend to this order.';
            $body.='<br /><br />Regards<br /><br />'.$storeName;
            
            foreach($emailArr as $toemail){
                if(!empty($toemail)){
                    $this->_sendMailcallEmail($subject, $body, $toemail, $senderEMAIL,$storeName);
                }
            }
        }
        return;
    }
    /*
     * send error report email
     */
    protected function _sendErrorReportEmail($kind,$lineno){
        $r = $this->_rawRequest;
        $msg=array('book'=>'Book Error','status'=>'Status Error','pricelist'=>'Pricelist Error');
        
        $senderEMAIL=Mage::getStoreConfig(self::XML_PATH_EMAIL_GENERAL.'email');
        $toemail=$this->_errorReportEmail;
        $lineno=empty($lineno)?'null':$lineno;
        
        $subject='API Connection Error Message';
        $body = $msg[$kind].":LineNo. (".$lineno.");  Date (".date('Ymd')."); Website (".Mage::getBaseUrl().") .";
        
        $this->_sendMailcallEmail($subject,$body,$toemail,$senderEMAIL);
        
        return;
    }
    
    protected function _contactApiErrorMsg($kind='book'){
        $msg=$this->getConfigData('specificerrmsg');//array('book'=>$this->getConfigData('specificerrmsg'),'status'=>'Sorry for the connection error, Please call MailCall IT Department. Bookings and Enquiries: 136 331');
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><response />');
        $errXml = $xml->addChild('errors');
        $errXml->addChild('api_error', $msg);
        
        return $xml->asXML();
        
    }
    public function getNowSystemTime(){
        return Mage::getModel('core/date')->timestamp(time());
    }
    
    protected function checkServiceTime() {
        $r = $this->_rawRequest;
        $serviceTime=$r->getServiceTime();
        if(!empty($serviceTime)){
            $_locale=$this->getNowSystemTime();
            $nowday=date('w',$_locale);
            $nowhour=date('H',$_locale);
            $nowMinute=date('i',$_locale);
            $nowhour=$nowhour.'.'.$nowMinute;

            $serviceTime=unserialize($serviceTime);
            if(!isset($serviceTime['allowday']) || !isset($serviceTime['allowhourfrom']) || !isset($serviceTime['allowhourto'])){
                $allowday=array('r0'=>array(1,2,3,4,5));
                $allowhourfrom=array('r0'=>array('09.00'));
                $allowhourto=array('r0'=>array('17.00'));
                
            }else{
                $allowday=$serviceTime['allowday'];
                $allowhourfrom=$serviceTime['allowhourfrom'];
                $allowhourto=$serviceTime['allowhourto'];
            }

            //debug log
            $debugData = array('nowday' => $nowday,'nowhour'=>$nowhour,'allowday'=>$allowday,'allowhourfrom'=>$allowhourfrom,'allowhourto'=>$allowhourto);
            $this->_debug($debugData);
            //check weekday
            $pass=false;
            foreach($allowday as $row=>$dayArr){
                $hourfrom=$allowhourfrom[$row][0];
                $hourto=$allowhourto[$row][0];
                if(in_array($nowday, $dayArr) && ($nowhour>=$hourfrom && $nowhour<=$hourto)){
                    $pass=true;
                    break;
                }
            }
            return $pass;
        }else{
            return true;
        }
    }
    protected function _checkApikeys(){
        $apikeys=$this->getConfigData('apikeys');
        $returnArr=array();
        if(!empty($apikeys)){
            $apikeys=unserialize($apikeys);
            $apikeyArr=$apikeys['apikey'];
            $homeaddressArr=$apikeys['homeaddress'];
            $suburbArr=$apikeys['suburb'];
            if(!empty($apikeyArr) && !empty($homeaddressArr) && !empty($suburbArr)){
                foreach($apikeyArr as $key=>$val){
                    $returnArr[$key]=array('apikey'=>$val,'homeaddress'=>$homeaddressArr[$key],'suburb'=>$suburbArr[$key]);
                }
            }
        }
        if(empty($returnArr)){
            $this->_debug('There is no available APIKEY.');
        }
        return $returnArr;
    }
    
    protected function _getXmlQuotes(){
        
        if(!$this->checkServiceTime()){
            $this->_debug('Now is not in service time.');
            return false;
        }
        
        $apikeys=$this->_checkApikeys();
        if(empty($apikeys)){
            $this->_debug('API KEY is empty.');
            return false;
        }
        $result='';
        foreach($apikeys as $key=>$val){
            $result=$this->_getXmlQuotesGOGO($val['apikey'],$key);
            if(!empty($this->_mailcallRates)){
                $this->getCheckout()->setStepData('shipping_method','mailcall_suburb',$val['suburb']);
                $this->getCheckout()->setStepData('shipping_method','mailcall_homeaddress',$val['homeaddress']);
                $this->getCheckout()->setStepData('shipping_method','mailcall_apikey',$val['apikey']);
                //print_r($this->getCheckout()->getStepData('shipping_method'));
                return $result;
                break;
            }
            $this->_fileName='pricelist.xml';
            $this->_errors=array();
        }
         
    }

    protected function _getXmlQuotesGOGO($apikey,$num){
        $r = $this->_rawRequest;

        $xml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><request type="pricelist" /> ');
        
        $request = $xml->asXML();
        $request = array('key' => $apikey, 'xml' => $request);
        $debugData['request_price'] = $request;
        try {
            $responseBody=$this->readXml($num);
            if(empty($responseBody)){
                for($i=1;$i<=$this->_retryTimes;$i++){
                    //request query price
                    $responseBody=$this->_submitPost($request);
                    if(!empty($responseBody)) break;
                }
                //$i=5;$responseBody="";
                //contact api error
                if($i>=5 && empty($responseBody)){
                    $responseBody=$this->_contactApiErrorMsg('pricelist');
                    $this->_sendErrorReportEmail('pricelist','');
                }else{
                    if(strlen($responseBody)>2000){
                        $this->generateXml($responseBody);
                    }
                }

                $debugData['retrytimes_price'] = $i;
            }
        }catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $responseBody = '';
        }

        $this->_debug($debugData);
        $res = $this->_parseXmlResponse($responseBody);

        return $res;
    }
    
    protected function _parseXmlResponse($response) {
        $r = $this->_rawRequest;
        $costArr = array();
        $priceArr = array();
        $errorTitle = 'Unable to retrieve quotes';

        $tr = get_html_translation_table(HTML_ENTITIES);
        unset($tr['<'], $tr['>'], $tr['"']);
        $response = str_replace(array_keys($tr), array_values($tr), $response);

        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                $xml = simplexml_load_string($response);

                if (is_object($xml)) {
                    if (
                            is_object($xml->errors)
                            && !empty($xml->errors)
                    ) {
                        $errors = get_object_vars($xml->errors);

                        foreach ($errors as $code => $des) {
                            if(is_object($des)){
                                $description=$des->text;
                            }else{
                                $description=$des;
                            }
                            $this->_errors[$code] = Mage::helper('webqemmailcall')->__('Error #%s : %s', $code, $description);
                        }
                    } else {
                        $suburbList=$xml->pricelist->suburb;
                        $okObj='';
                        foreach($suburbList as $obj){
                            if($obj->postcode==$r->getDestPostal() && $obj->name==strtoupper($r->getDestCity())){
                                $okObj=$obj;
                                break;
                            }
                        }

                       $this->_parseXmlObject($okObj);
                       
                    }
                }
            } else {
                $this->_errors[] = Mage::helper('webqemmailcall')->__('The response is in wrong format.');
            }
        }

//        if (count($this->_errors)>1) {
//            return false;
//        }

        $result = Mage::getModel('shipping/rate_result');

        foreach ($this->_errors as $errorText) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorText);
            //$error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }

        foreach ($this->_mailcallRates as $rate) {
            $method = $rate['service'];
            $data = $rate['data'];
            $rate = Mage::getModel('shipping/rate_result_method');
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethod($method);
            $rate->setMethodTitle($data['term']);
            $rate->setCost($data['price_total']);
            $rate->setPrice($data['price_total']);
            $result->append($rate);
        }

        return $result;
    }

    protected function _parseXmlObject($shipXml) {
        if(!is_object($shipXml) || empty($shipXml)){
            return false;
        }
        $nowTime=$this->_getLocale()->storeTimeStamp();
        $debugData['result_price'] = $shipXml;
        $debugData['date'] = date('Y-m-d H:i:s',$nowTime);
        $this->_debug($debugData);
        
        $this->_addRate($shipXml);
        return $this;
    }

    protected function _addRate($shipXml) {
        $r = $this->_rawRequest;

        $desc = $r->getContentDesc();
        $totalEstimate = (string) $shipXml->totalcost;
        
        if($r->getUsefixedcost()){
            $distance=(string) $shipXml->distance;
            if($distance<=$r->getWithinkms()){
                $totalEstimate=$r->getFixedcost();
            }else{
                if(!$r->getDisplayWangitnow()){
                    return false;
                }
            }
        }

        /*
         * mailcall can return with empty result and success code
         * we need to make sure there is shipping estimate and code
         */
        if ($totalEstimate>=0) {
            if($r->getMailcallFreeShipping()==-1){
				$totalEstimate='0.00';
			}
			
			$service = $this->_code;
			//$data['term'] = $desc . ' ('.$shipXml->DeliveryDate.')';
            $data['term'] = $desc;
            $data['price_total'] = $this->getMethodPrice($totalEstimate, $service);
            $this->_mailcallRates[] = array('service' => $service, 'data' => $data);
            $this->getCheckout()->setStepData('shipping_method','mailcall_homepostal',(string)$shipXml->home);
            
        }
    }
    
    protected function _submitPost($request){
        $url = $this->_mailcallGatewayUrl;
        if ($this->getConfigData('debug')) {
            $url = $this->_defaultGatewayUrl;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $responseBody = curl_exec($ch);
        curl_close($ch);
        return $responseBody;
    }
    
    protected function _getXmlString($response,$xbody='lineno'){
        $str='';
        if(strpos($response, '</'.$xbody.'>')!==false && strpos($response, '<'.$xbody.'>')!==false){
            $tmpXml=explode('</'.$xbody.'>',$response);
            $tmpXml=explode('<'.$xbody.'>',$tmpXml[0]);
            $str=$tmpXml[1];
        }
        return $str;
    }
	
    protected function getCheckout(){
            return Mage::getSingleton('checkout/session');
    }
    
    
    public function bookXmlRequest($quote) {
        $order=$this->getOrder();
        $address=$order->getShippingAddress();
        $street=$address->getStreet();
        $streetStr='';
        if(is_array($street)){
            foreach($street as $val){
                $streetStr.=$val.' ';
            }
            $streetStr=substr($streetStr, 0, strlen($streetStr)-1);
        }else{
            $streetStr=$street;
        }
        $pickup = Mage::getSingleton('checkout/session')->getPickup();
        $xml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><request xmlns="http://www.mailcall.com.au" type="book" version="1.4" />');

        $timeslotModel = Mage::getModel('webqemmailcall/timeslot')->load($pickup['timeslot']);
        $readytime= str_replace(':', '', $timeslotModel->getTimeStart());
    
        $suburb=$this->getCheckout()->getStepData('shipping_method','mailcall_suburb');
        $homeaddress=$this->getCheckout()->getStepData('shipping_method','mailcall_homeaddress');
        $homepostal=$this->getCheckout()->getStepData('shipping_method','mailcall_homepostal');
        $apikey=$this->getCheckout()->getStepData('shipping_method','mailcall_apikey');
        
        //echo $this->getConfigData('fromaddress');
        
        
        $getQuote = $xml->addChild('job');
        $requestor = $getQuote;
        $requestor->addChild('date', date('Ymd',strtotime($pickup['timeslot_date'])));        
        $requestor->addChild('fromcompany', $this->getConfigData('fromcompany'));
        $requestor->addChild('fromaddress1', $homeaddress);//$this->getConfigData('fromaddress')
        $requestor->addChild('fromcontact', $this->getConfigData('fromcontact'));
        $requestor->addChild('fromphone', $this->getConfigData('fromphone'));
        $requestor->addChild('fromsuburb', $suburb);//$this->getConfigData('fromsuburb')
        $requestor->addChild('frompostcode', $homepostal);//$this->getConfigData('frompostcode')
        $requestor->addChild('tocompany', $address->getCompany());
        $requestor->addChild('toaddress1', $streetStr);
        $requestor->addChild('tocontact', $address->getFirstName().' '.$address->getLastName());
        $requestor->addChild('tophone', $address->getTelephone());
        $requestor->addChild('tosuburb', $address->getCity());
        $requestor->addChild('topostcode', $address->getPostcode());
        $requestor->addChild('service', 'RUNS');
        $requestor->addChild('vehicle', 'C');
        $requestor->addChild('weight', $order->getWeight());
        $requestor->addChild('weightunits', 'kg');
        $requestor->addChild('sizeclass', 'Archive Box');
        $requestor->addChild('readytime', $readytime);
        $requestor->addChild('driverinstructions', '');
        $requestor->addChild('reference', '');
        $requestor->addChild('consignment', '');
        
        $options = $requestor->addChild('options');
        $options->addChild('warehousesms',  $this->getConfigData('warehouse_sms_nofify') ? 'Y' : 'N');
        $options->addChild('custdeldispatchsms', isset($pickup['sms_dispatched']) ? 'Y' : 'N' );
        $options->addChild('custdelimminentsms', isset($pickup['sms_time_away']) ? 'Y' : 'N' );
        $options->addChild('custsmsphone',$address->getTelephone());
        $options->addChild('warehousesmsphone', $this->getConfigData('sms_contact_number'));
         
        $items = $requestor->addChild('items');
        $i = 0;
        foreach ($order->getAllItems() as $_item) {
            $productId= $_item->getProductId();
            if(!empty($productId)) {
                for ($j = 0; $j < $_item->getQtyOrdered(); $j++) {
                    $i++;
                    $_product = Mage::getModel('catalog/product')->load($productId);
                    $item = $items->addChild('item');
                    $item->addChild('itemref', $_product->getSku());
                    $item->addChild('length', number_format($_product->getLength(), 3));
                    $item->addChild('width', number_format($_product->getWidth(), 3));
                    $item->addChild('height', number_format($_product->getHeight(), 3));
                    $item->addChild('weight', number_format($_product->getWeight(), 3));
                    $item->addChild('barcode', $_product->getBarcode());
                
                    
                }
                
            }
        }

        $requestor->addChild('quantity', $quote->getItemsQty());
        $requestor->addChild('oktoleave', 'N');
        $requestor->addChild('echo', 'N');
        
        $request = $xml->asXML();
        $request = array('key' => $apikey, 'xml' => $request);
        $debugData['request_book'] = $request;
//        Mage::log($requestor);die;
        $privatelink='';
        try {
            
            for($i=1;$i<=$this->_retryTimes;$i++){
                //request book
                $responseBody=$this->_submitPost($request);
                if(!empty($responseBody)) break;
            }
            Mage::log($requestor);
            Mage::log($responseBody);
            //$i=5;$responseBody="";
            //contact api error
            if($i>=5 && empty($responseBody)){
                $responseBody=$this->_contactApiErrorMsg('book');
                $this->_sendErrorReportEmail('book','');
            }
            
            $debugData['result_book'] = $responseBody;
            $debugData['retrytimes_book'] = $i;
            
            $privatelink=$this->_getXmlString($responseBody,'privatelink');
            
            //added by Mike @ Mailcall 01/06/2012
            $wintracklink=$this->_getXmlString($responseBody,'wintracklink');
            $linenumber=$this->_getXmlString($responseBody,'lineno');
            $mobileauthcode=$this->_getXmlString($responseBody,'mobileauthcode');
            
            //check lineno
            $lineno='';//$this->_getXmlString($responseBody);
            if(!empty($lineno) && false){
                //request status
                $statusXml = new SimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><request xmlns="http://www.mailcall.com.au" type="status" version="1.4" />');
                $newJob = $statusXml->addChild('job');
                $newJob->addChild('lineno', $lineno);
                $newJob->addChild('date', date('Ymd',$nowTime));

                $statusRequest = $statusXml->asXML();
                $statusRequest = array('key' => $this->getConfigData('apikey'), 'xml' => $statusRequest);
                $debugData['request_status'] = $statusRequest;
                
                //$this->_submitPost($statusRequest);
                        
                for($i=1;$i<=$this->_retryTimes;$i++){
                    $responseBody=$this->_submitPost($statusRequest);
                    $error=$this->_getXmlString($responseBody,'error');
                    if(!empty($responseBody) && empty($error)) break;
                }
                //$i=5;$responseBody="";
                //contact api error
                if($i>=5 && empty($responseBody)){
                    $responseBody=$this->_contactApiErrorMsg('status');
                    $this->_sendErrorReportEmail('status',$lineno);
                }

                $debugData['result_status'] = $responseBody;
                $debugData['retrytimes_status'] = $i;
                
		

            }
            
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $responseBody = '';
        }

        $this->_debug($debugData);
        //Modified by Mike @ Mailcall 01/06/2012
        $res=$this->_parseBookXmlResponse($responseBody,$privatelink,$wintracklink,$linenumber,$mobileauthcode);
        //$res=$this->_parseBookXmlResponse($responseBody,$privatelink);
        
        return $res;
    }
    
   //Modified by Mike @ Mailcall 01/06/2012
    protected function _parseBookXmlResponse($response,$privatelink,$wintracklink,$linenumber,$mobileauthcode) {
   // protected function _parseBookXmlResponse($response,$privatelink) {
   
        $costArr = array();
        $priceArr = array();
        $errorTitle = 'Unable to retrieve quotes';

        $tr = get_html_translation_table(HTML_ENTITIES);
        unset($tr['<'], $tr['>'], $tr['"']);
        $response = str_replace(array_keys($tr), array_values($tr), $response);

        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                $xml = simplexml_load_string($response);

                if (is_object($xml)) {
                    if (
                            is_object($xml->errors)
                            && !empty($xml->errors)
                    ) {
                        $errors = get_object_vars($xml->errors);

                        foreach ($errors as $code => $des) {
                            if(is_object($des)){
                                $description=$des->text;
                            }else{
                                $description=$des;
                            }
                            if(strlen($description)>=3){
                                $this->_errors[$code] = $description;
                            }
                        }
                    } 
                }
            } else {
                $this->_errors[] = Mage::helper('webqemmailcall')->__('The response is in wrong format.');
            }
        }

        $errorMsg="";$num=1;
        foreach ($this->_errors as $errorText) {
             $errorMsg.=$num.'.'.$errorText.';  ';
             $num++;
        }
        if(!empty($errorMsg)){
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = 'Error:'.$errorMsg;
            echo Zend_Json::encode($result);
            exit;
        }else{
            //Modified by Mike @ Mailcall 01/06/2012
            $this->sendNewOrderEmailToCustomer($privatelink,$wintracklink,$linenumber,$mobileauthcode);
            //$this->sendNewOrderEmailToCustomer($privatelink);
            $this->sendNewOrderNoticeEmail();
        }

        return $this;
    }

    protected function _getShipDate($domestic=true) {
        if ($domestic) {
            $days = explode(',', $this->getConfigData('shipment_days'));
        } else {
            $days = explode(',', $this->getConfigData('intl_shipment_days'));
        }

        if (!$days) {
            return date('Y-m-d');
        }

        $i = 0;
        $weekday = date('w');
        while (!in_array($weekday, $days) && $i < 10) {
            $i++;
            $weekday = date('w', strtotime("+$i day"));
        }

        return date('Y-m-d', strtotime("+$i day"));
    }

    protected function _getInsuredValue($total = 0) {

        
    }

    /**
     * Check if carrier has shipping tracking option available
     * All Mage_Usa carriers have shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable() {
        return true;
    }

    /**
     * Get tracking message from mailcall
     * @var $trackings string
     */
    public function getTracking($trackings) {

        //$this->_trackingErrTips();
        return false;//$this->_result;
        
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }
        $this->_getXMLTracking($trackings);

        return $this->_result;
    }
    
    protected function _trackingErrTips(){
        $result = Mage::getModel('shipping/tracking_result');
        $errorTitle = Mage::helper('webqemmailcall')->__('Want it now');
        
        $error = Mage::getModel('shipping/tracking_result_error');
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setTracking($errorTitle);
        $error->setErrorMessage('Please go to Mail call Website Query.(www.mailcall.com.au)');
        $result->append($error);
        
        $this->_result = $result;
    }

    protected function setTrackingReqeust() {
        $r = new Varien_Object();

        $id = $this->getConfigData('id');
        $r->setId($id);

        $password = $this->getConfigData('password');
        $r->setPassword($password);

        $this->_rawTrackRequest = $r;
    }

    public function getTrackingInfo($tracking) {
        $info = array();

        $result = $this->getTracking($tracking);

        if ($result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    protected function _getXMLTracking($trackings) {
        
        
    }

    protected function _parseXmlTrackingResponse($trackings, $response) {
        
    }
    

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     */
    public function _debug($debugData) {
        if ($this->getDebugFlag()) {
            $logfilename = 'shipping_' . $this->getCarrierCode() . '.log';
            Mage::log($debugData, null, $logfilename, true);
        }
    }
    
    /**
     * Retrieve current locale
     *
     * @return Mage_Core_Model_Locale
     */
    protected function _getLocale()
    {
        return Mage::app()->getLocale();
    }
    
    /**
     * Getter for carrier code
     *
     * @return string
     */
    public function getCarrierCode() {
        return $this->_code;
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag() {
        return $this->getConfigData('debug');
    }

    public function isStateProvinceRequired() {
        return true;
    }
    
    protected function getPath()
    {
        return str_replace('//', '/', Mage::getBaseDir() .$this->_filePath);
    }
    /**
     * Generate pricelist xml file
     */
    public function generateXml($xmlBody)
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));

        if ($io->fileExists($this->_fileName) && !$io->isWriteable($this->_fileName)) {
            Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->_fileName, $this->getPath()));
        }

        $io->streamOpen($this->_fileName);

        $io->streamWrite($xmlBody);
        $io->streamClose();

        return $this;
    }
    
    /**
     * get the time of generate pricelist xml file 
     */
    public function canUpdatePriceApi(){
        
        $fileTime=Mage::getSingleton('core/date')->timestamp(filemtime($this->getPath().$this->_fileName));
        $nowTime=Mage::getSingleton('core/date')->timestamp();
        $day=round(($nowTime-$fileTime)/(3600*24),1);
        if($day>=$this->_updatePriceDays){
            return true;
        }
        
        return false;
    }

    /**
     * Read pricelist xml file
     */
    public function readXml($num)
    {
        $this->_fileName=str_replace('pricelist.xml','pricelist'.$num.'.xml',$this->_fileName);
        
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $xml='';
        if ($io->fileExists($this->_fileName) && !$this->canUpdatePriceApi()) {
            $xml=$io->read($this->_fileName);
        }
        $io->streamClose();
        return $xml;
    }
    
    public function getAllowedMethods()
    {
        return array($this->_code=>$this->getConfigData('title'));
    }

}