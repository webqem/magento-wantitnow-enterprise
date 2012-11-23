<?php

class Webqem_Mailcall_Model_Mysql4_Pickup extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the pickup_id refers to the key field in your database table.
        $this->_init('webqemmailcall/pickup', 'id');
    }
}