<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getPostUrl()
    {
        return $this->_getUrl('mailcall/post');
    }
}