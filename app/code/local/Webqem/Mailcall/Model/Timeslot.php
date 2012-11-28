<?php

class Webqem_Mailcall_Model_Timeslot extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('webqemmailcall/timeslot');
    }
    public function getCollection()
    {
    	$collection = parent::getCollection();
    	$collection->setOrder('number_day','ASC');
    	$collection->setOrder('time_start','ASC');
    	return $collection;
    }
    public function getAllTimeslotByDay($day)
    {
    	$collection = $this->getCollection()->addFieldToFilter('number_day', $day);
    	
    	return $collection->getItems();
    }
}