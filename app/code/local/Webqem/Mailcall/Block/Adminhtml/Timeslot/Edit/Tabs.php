<?php

class Webqem_Mailcall_Block_Adminhtml_Timeslot_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('fbcp_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('webqemmailcall')->__('Timeslot Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('webqemmailcall')->__('Timeslot Information'),
          'title'     => Mage::helper('webqemmailcall')->__('Timeslot Information'),
          'content'   => $this->getLayout()->createBlock('webqemmailcall/adminhtml_timeslot_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}