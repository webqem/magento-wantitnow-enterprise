<?php
class Webqem_Mailcall_Block_Adminhtml_Timeslot extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_timeslot';
		$this->_blockGroup = 'webqemmailcall';
		$this->_headerText = Mage::helper('webqemmailcall')->__('Timeslot Manager');
		$this->_addButtonLabel = Mage::helper('webqemmailcall')->__('Add Timeslot');
		parent::__construct();
	}
}