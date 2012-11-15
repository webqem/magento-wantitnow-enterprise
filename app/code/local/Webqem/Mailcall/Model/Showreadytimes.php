<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Showreadytimes
{
    protected $_showHours=array('');
    
    public function toOptionArray()
    {
        return array(
            '1'    => Mage::helper('webqemmailcall')->__('Fixed Time'),
            '2'    => Mage::helper('webqemmailcall')->__('Current Time + x hours')
            
        );
    }
}