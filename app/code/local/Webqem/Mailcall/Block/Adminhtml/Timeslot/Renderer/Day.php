<?php
class Webqem_Mailcall_Block_Adminhtml_Timeslot_Renderer_Day 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
	public function render(Varien_Object $row)
	{
		return $this->_getValue($row);
	}	
	public function _getValue(Varien_Object $row)
	{
		$data = $row->getData();
		
		$arrDay = Mage::getModel('webqemmailcall/timeslot_listdate')->getOptionArray();
		return $arrDay[$data['number_day']];
	}
	
}