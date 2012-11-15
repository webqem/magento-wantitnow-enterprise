<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Displaywantitnow
{
    public function toOptionArray()
    {
        return array(
            0    => Mage::helper('webqemmailcall')->__('Not offer Want It Now'),
            1  => Mage::helper('webqemmailcall')->__('Use standard Want It Now rates')
        );
        
    }
}