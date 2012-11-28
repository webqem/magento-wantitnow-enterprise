<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Model_Timeslot_Days
{
    protected $_showHours=array('');
    
    public function toOptionArray()
    {
        return array(
            '0'    => 0,
            '1'    => 1, 
        	'2'    => 2,
        	'3'    => 3,
        	'4'    => 4,
        	'5'    => 5,
        );
    }
    
}