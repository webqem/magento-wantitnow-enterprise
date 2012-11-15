<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    
    public function getCarrierName($carrierCode)
    {
        if($carrierCode=='webqemmailcall'){
            $mailcall_url=$this->getUrl('media/wantitnow',array('_secure'=>true));
            $mailcall_url=str_replace('/index.php','',$mailcall_url);
            $logo='<img alt="'.Mage::helper('webqemmailcall')->__('Want it now').'" src="'.$mailcall_url.Mage::getStoreConfig('carriers/'.$carrierCode.'/display_logos').'.png">';
                       //commented and changed by Mike @ Mailcall 02/08/2012
                        //return $logo;
                        echo $logo;
        }else{
            if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
                //commented and changed by Mike @ Mailcall 02/08/2012
                //return $logo;
                echo $name;
            }
        }
        //commented and changed by Mike @ Mailcall 02/08/2012
        //return $carrierCode;
    }

}