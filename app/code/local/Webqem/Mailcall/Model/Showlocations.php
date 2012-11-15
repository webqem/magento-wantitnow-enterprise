<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Showlocations
{
    protected $_showHours=array('');
    
    public function toOptionArray()
    {
        return array(
            '2065'    => Mage::helper('webqemmailcall')->__('Sydney'),
            '3144'    => Mage::helper('webqemmailcall')->__('Melbourne'),  
            'all'    => Mage::helper('webqemmailcall')->__('Sydney and Melbourne'), 
            
        );
    }
}