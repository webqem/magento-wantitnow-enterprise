<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Timeslot_Listdate extends Varien_Object
{
   
    static public function getOptionArray()
    {
        return array(
        		1=> Mage::helper('webqemmailcall')->__('Monday'),
        		2=> Mage::helper('webqemmailcall')->__('Tuesday'),
        		3=> Mage::helper('webqemmailcall')->__('Wednesday'),
        		4=> Mage::helper('webqemmailcall')->__('Thursday'),
        		5=> Mage::helper('webqemmailcall')->__('Friday')
        );
    }
}