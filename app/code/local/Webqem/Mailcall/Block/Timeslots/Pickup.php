<?php
class Webqem_Mailcall_Block_Timeslots_Pickup extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
	public function __construct()
	{
		$this->setTemplate('webqem/mailcall/timeslots/pickup.phtml');
	}
	public function getConfigData($field)
	{
		$path = 'carriers/timeslot/'.$field;
		return Mage::getStoreConfig($path, $this->getStore());
	}
	
	public function getDisableDate($encode = false)
	{
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$billingAddress = $quote->getBillingAddress();
		$regionId = $billingAddress->getRegionId();
		
		$region = Mage::getModel('directory/region')->load($regionId);
		
		$holidays = Mage::getModel('webqemmailcall/holidays')->getCollection();
		$holidays->getSelect()->where('holidays_state="'.$region->getCode().'" AND 	holidays_status=1');
		
		$arrHolidays = array();
		foreach ($holidays as $holiday) {
			$arrHolidays[] = date('m/j/Y', strtotime($holiday->getHolidaysDate()));
			
		}
		if ($encode)
			return $arrHolidays;
		return json_encode($arrHolidays);
	}
}