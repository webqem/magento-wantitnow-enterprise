<?php

class Webqem_Mailcall_Block_Adminhtml_Timeslot_Edit_Tab_Form extends Mage_Core_Block_Template 
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	
	public function __construct(){
	  	$this->setTemplate('webqem/timeslot.phtml');
	  	parent::__construct();
	 }
	 //Label to be shown in the tab
	 public function getTabLabel(){
	 	return Mage::helper('core')->__('Timeslot day');
	 }
	 
	 public function getTabTitle(){
	 	return Mage::helper('core')->__('Timeslot');
	 }
	 
	 public function canShowTab(){
	 	return true;
	 }
	 
	 public function isHidden(){
	 	return false;
	 }
	 protected function getHours(){
	 	$_hours=array();
	 	$_minute=array('00','30');
	 	for($i=6;$i<24;$i++){
	 		for($j=0;$j<=1;$j++){
	 			$hh=str_pad($i,2,"0",STR_PAD_LEFT);
	 			$mm=$_minute[$j];
	 			$_hours[$hh.':'.$mm]=$hh.':'.$mm;
	 		}
	 	}
	 	return $_hours;
	 }
	 public function getOptionHour($selected = null)
	 {
	 	$html = '';
	 	foreach ($this->getHours() as $key=>$value) {
	 		if ($selected==$key) {
	 			$html .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
	 		} else {
	 			$html .= '<option value="'.$key.'">'.$value.'</option>';
	 		}
	 	}
	 	
	 	return $html;
	 }
	 public function getNumberDayExisted()
	 {
	 	$arrNumber = array();
	 	$numberDay = Mage::registry('timeslot_data')->getNumberDay();
	 	
	 	$collection = Mage::getModel('webqemmailcall/timeslot')->getCollection();	 
	 	$collection->getSelect()->group('number_day');
	 	
	 	foreach ($collection as $timeslot) {
	 		if ($numberDay == $timeslot->getNumberDay()) {
	 			continue;
	 		}
	 		$arrNumber[] = $timeslot->getNumberDay();
	 	}
	 	
	 	return $arrNumber;
	 }
	 public function getOptionday()
	 {
	 	$arrData = Mage::getModel('webqemmailcall/timeslot_listdate')->getOptionArray();
	 	 	
	 	$arrNumberDayExisted = $this->getNumberDayExisted();
	 	if (count($arrNumberDayExisted)>0) {
	 		foreach ($arrData as $k=>$v) {
	 			if (in_array($k, $arrNumberDayExisted)) {
	 				unset($arrData[$k]);
	 			}
	 		}
	 	
	 	}
	 	$html = "<select name='timeslot_day' class='timeslot_day'>";
	 	foreach ($arrData as $k=>$v) {
	 		if (Mage::registry('timeslot_data')->getNumberDay() == $k) {
	 			$html .= "<option value='$k' selected='selected'>$v</option>";
	 		} else {
	 			$html .= "<option value='$k'>$v</option>";
	 		}
	 	}
	 	
	 	$html .= "</select>";
	 	return $html;
	 }
	 public function getDataEdit()
	 {
	 	return Mage::registry('timeslot_edit_data');
	 }
}