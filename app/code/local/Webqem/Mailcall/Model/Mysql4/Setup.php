<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function _construct()
    {    
        // Note that the export_id refers to the key field in your database table.
        $this->_init('webqem/webqemmailcall');
    }
}