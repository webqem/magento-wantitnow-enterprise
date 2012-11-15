<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Showhours
{
    protected $_showHours=array('');
    
    public function toOptionArray()
    {
        $methods = Mage::helper('payment')->getStoreMethods(Mage::app()->getStore()->getId());
        $options = array();
        foreach ($methods as $method)
        {
            array_unshift($options, array(
                'value' => $method->getCode(),
                'label' => $method->getTitle(),
            ));
        }
        array_unshift($options, array(
                'value' => '',
                'label' => Mage::helper('webqemmailcall')->__('Allow All Payment Methods'),
            ));
        return $options;
    }
}