<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Showlogos
{
    
    public function toOptionArray()
    {
        return array(
            'witblack'    => Mage::helper('webqemmailcall')->__('Black'),
            'witblue'    => Mage::helper('webqemmailcall')->__('Blue'),
            'witwhite'    => Mage::helper('webqemmailcall')->__('White'),
            'witcarblack'    => Mage::helper('webqemmailcall')->__('Car black'),
            'witcarblue'    => Mage::helper('webqemmailcall')->__('Car blue'),
            'witcarwhite'    => Mage::helper('webqemmailcall')->__('Car white')       
            
        );
    }
}