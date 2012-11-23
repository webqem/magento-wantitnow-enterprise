<?php

class Webqem_Mailcall_Block_Adminhtml_Timeslot_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'webqemmailcall';
        $this->_controller = 'adminhtml_timeslot';
        
        $this->_updateButton('save', 'label', Mage::helper('webqemmailcall')->__('Save Timeslot'));
        $this->_updateButton('delete', 'label', Mage::helper('webqemmailcall')->__('Delete Timeslot'));
		
    }

    public function getHeaderText()
    {
        if( Mage::registry('timeslot_data') && Mage::registry('timeslot_data')->getId() ) {
            return Mage::helper('webqemmailcall')->__("Edit Timeslot");
        } else {
            return Mage::helper('webqemmailcall')->__('Add Timeslot');
        }
    }
}