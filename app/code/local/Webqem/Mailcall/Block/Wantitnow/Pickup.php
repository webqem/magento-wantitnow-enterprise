<?php
class Webqem_Mailcall_Block_Wantitnow_Pickup extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
	public function __construct()
	{
		$this->setTemplate('webqem/mailcall/wantitnow/pickup.phtml');
	}
	public function getConfigData($field)
	{
		$path = 'carriers/webqemmailcall/'.$field;
		return Mage::getStoreConfig($path, $this->getStore());
	}
}