<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Block_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    
    /**
     * Check and prepare payment method model
     *
     * @return bool
     */
    protected function _canUseMethod($method)
    {
        $mailcall_payment=$this->getCheckout()->getStepData("shipping_method",'mailcall_payment');
        if(!empty($mailcall_payment)){
            if(!in_array($method->getCode(), explode(',', $mailcall_payment))){
                return false;
            }
        }
        
        return parent::_canUseMethod($method);
    }
    public function getCheckout(){
        return Mage::getSingleton('checkout/session');
    }

 
}
