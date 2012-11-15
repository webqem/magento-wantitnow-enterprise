<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Allowedproducts
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('webqemmailcall')->__('All products')),
            array('value'=>1, 'label'=>Mage::helper('webqemmailcall')->__('Specific Products')),
        );
    }
}