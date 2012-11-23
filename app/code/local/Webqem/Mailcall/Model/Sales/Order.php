<?php
class Webqem_Mailcall_Model_Sales_Order extends Mage_Sales_Model_Order{
	public function getShippingDescription(){
		$desc = parent::getShippingDescription();
		$pickupObject = $this->getPickupObject();
		if($pickupObject){
			$timeslotId = $pickupObject->getTimeslot();
			
			$timeslot = Mage::getModel('webqemmailcall/timeslot')->load($timeslotId);
			
			$desc .= ' - Delivery: '.$timeslot->getDescription().' ' . $timeslot->getTimeStart() . ' to ' . $timeslot->getTimeEnd().'( '.$pickupObject->getTimeslotDate().' )';
			
			
		}
		return $desc;
	}
}