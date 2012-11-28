<?php

class Webqem_Mailcall_Model_System_Config_Backend_Shipping_Holidays extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
        Mage::getResourceModel('webqemmailcall/holidays')->uploadAndImport($this);
    }
}
