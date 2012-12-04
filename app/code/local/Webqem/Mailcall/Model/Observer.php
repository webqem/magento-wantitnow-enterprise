<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Webqem_Mailcall_Model_Observer
{
    public function setMailcallShippingMethod($observer){
        $event 	= $observer->getEvent();
        $request=$event->getRequest();
        $pickup = $request->getParam('shipping_pickup',false);
        if($pickup){
        	Mage::getSingleton('checkout/session')->setPickup($pickup);
        }
        
        $shippingMethod=$request->getPost('shipping_method', '');
        
        if($shippingMethod=='webqemmailcall_webqemmailcall'){
            $this->getCheckout()->setStepData('shipping_method','use_mailcall',1);
	   		$this->getCheckout()->setStepData('shipping_method','mailcall_payment',Mage::getStoreConfig('carriers/webqemmailcall/payment'));
        }else{
            $this->getCheckout()->setStepData('shipping_method','use_mailcall',0);
            $this->getCheckout()->setStepData('shipping_method','mailcall_payment','');
        }
	
    }
    public function saveOrderAfter($evt){
    	$order = $evt->getOrder();
    	$pickup = Mage::getSingleton('checkout/session')->getPickup();
    	if(isset($pickup)){
    		$pickup['order_id'] = $order->getId();
    		$pickupModel = Mage::getModel('webqemmailcall/pickup');
    		$pickupModel->setData($pickup);
    		$pickupModel->save();
    	}
    }
    public function loadOrderAfter($evt){
    	$order = $evt->getOrder();
    	if($order->getId()){
    		$order_id = $order->getId();
    		$pickupCollection = Mage::getModel('webqemmailcall/pickup')->getCollection();
    		$pickupCollection->addFieldToFilter('order_id',$order_id);
    		$pickup = $pickupCollection->getFirstItem();
    		$order->setPickupObject($pickup);
    	}
    }
    public function loadQuoteAfter($evt)
    {
    	$quote = $evt->getQuote();
    	if($quote->getId()){
    		$quote_id = $quote->getId();
    		$pickup = Mage::getSingleton('checkout/session')->getPickup();
    		if(isset($pickup[$quote_id])){
    			$data = $pickup[$quote_id];
    			$quote->setPickupData($data);
    		}
    	}
    }
    public function getCheckout(){
        return Mage::getSingleton('checkout/session');
    }
	
    // get request book for saving
	public function saveRequestBook($observer) {
		$useMailcall=$this->getCheckout()->getStepData('shipping_method','use_mailcall');
		$order = $observer->getOrder();
		
		$strRequest = "";
		$shippingMethod = $order->getShippingMethod();
		
		
		if($useMailcall) {
			$event = $observer->getEvent();
			$order = $event->getOrder();
			$quote = $event->getQuote();

			$mailcallModel = Mage::getModel('webqemmailcall/carrier_mailcall');
	
			$strRequest = $mailcallModel->setOrder($order)
								->getbookXmlRequest($quote);
		}
		 
		if ($shippingMethod == 'timeslot_timeslot') {
			$event = $observer->getEvent();
			$order = $event->getOrder();
			$quote = $event->getQuote();
			$timeslotsModel = Mage::getModel('webqemmailcall/carrier_timeslots');
			$strRequest = $timeslotsModel->setOrder($order)
								->getbookXmlRequest($quote);
			 
		}
		$data['order_id'] 		 = $order->getData('increment_id');
		$data['shipping_method'] = $shippingMethod;
		$data['request']  		 = $strRequest;
		
		$requestModel = Mage::getModel('webqemmailcall/request')->setData($data)->save();
		
		return;
	}
    public function requestBookToMailcall($observer){
    	
        $order 		 = $observer->getEvent()->getOrder();
        $orderId = $order->getIncrementId();
        if (!$orderId) {
        	$orderId = $order->getId();
        }
        $orderStatus = Mage::getModel('sales/order')->loadByIncrementId($order->getIncrementId())
			        								->getStatus();
        if (!$orderStatus) {
        	$orderStatus = Mage::getModel('sales/order')->load($orderId)
			        		->getCollection()
			        		->getFirstItem()
			        		->getStatus();
        }
        $shippingMethod = $order->getShippingMethod();
       
        if ($orderStatus == Mage_Sales_Model_Order::STATE_PROCESSING) {
	        if($shippingMethod == 'webqemmailcall_webqemmailcall'){
	                $mailcallModel=Mage::getModel('webqemmailcall/carrier_mailcall');
	                $mailcallModel->bookXmlRequest($order);
	        }
	        
	        if($shippingMethod == 'timeslot_timeslot') {
	               $timeslotsModel = Mage::getModel('webqemmailcall/carrier_timeslots');
	               $timeslotsModel->bookXmlRequest($order);
	            
	        }
	       
        }
        $requestModel = Mage::getModel('webqemmailcall/request')->getCollection()
					        ->addFieldToFilter('order_id', $order->getIncrementId())
					        ->addFieldToFilter('status', 0)
					        ->getFirstItem();
        $model = Mage::getModel('webqemmailcall/request')->load($requestModel->getId());
        $model->setData('status', 1);
        $model->save();
        return;
    }
	
	public function salesruleActionsPrepareform($observer){
		$form = $observer->getEvent()->getForm();
		$fieldset = $form->getElement('action_fieldset');
		$fieldset->removeField('simple_free_shipping');
		$fieldset->addField('simple_free_shipping', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Free shipping'),
            'title'     => Mage::helper('salesrule')->__('Free shipping'),
            'name'      => 'simple_free_shipping',
            'options'    => array(
                0 => Mage::helper('salesrule')->__('No'),
                Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM => Mage::helper('salesrule')->__('For matching items only'),
                Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS => Mage::helper('salesrule')->__('For shipment with matching items'),
				Webqem_Mailcall_Model_Carrier_Mailcall::MAILCALL_FREE_SHIPPING_PROMO => Mage::helper('salesrule')->__('For Want it Now Shipping Method'),
            ),
        ));
		
	}
}
?>
