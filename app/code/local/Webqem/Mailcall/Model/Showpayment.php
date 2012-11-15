<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Showpayment
{
    
    public function toOptionArray()
    {
        $methods = Mage::helper('payment')->getStoreMethods(Mage::app()->getStore()->getId());
        $options = array();
        foreach ($methods as $method)
        {
            $mCode=$method->getCode();
            $mTitle=trim(strip_tags($method->getTitle()));
            $mTitle=empty($mTitle)?$mCode:$mTitle;
            array_unshift($options, array(
                'value' => $mCode,
                'label' => $mCode,
            ));
        }
        array_unshift($options, array(
                'value' => '',
                'label' => Mage::helper('webqemmailcall')->__('Allow All Payment Methods'),
            ));
        return $options;
    }
}