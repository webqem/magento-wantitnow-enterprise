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
	
    public function requestBookToMailcall($observer){
        $useMailcall=$this->getCheckout()->getStepData('shipping_method','use_mailcall');
        $order = $observer->getOrder();
        
        if($useMailcall){
                $event = $observer->getEvent();
                $order=$event->getOrder();
                $quote=$event->getQuote();
                //$privatelink=$this->getCheckout()->getStepData('shipping_method','mailcall_privatelink');

                $mailcallModel=Mage::getModel('webqemmailcall/carrier_mailcall');

                $mailcallModel->setOrder($order)
                              ->bookXmlRequest($quote);

        }
        
        if ($order->getShippingMethod() == 'timeslot_timeslot') {
            $event = $observer->getEvent();
                $order=$event->getOrder();
                $quote=$event->getQuote();
                $timeslotsModel = Mage::getModel('webqemmailcall/carrier_timeslots');
                $timeslotsModel->setOrder($order)
                              ->bookXmlRequest($quote);
            
        }

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
