<?php

class Webqem_Mailcall_Model_Mysql4_Timeslot extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('webqemmailcall/timeslot', 'timeslot_id');
    }
    
}