<?php 
class Webqem_Mailcall_Block_Adminhtml_System_Config_Form_Field_Link extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('webqemmailcall/adminhtml_timeslot/index');

        
        return "<a href='$url' target='_blank'>Manage Timeslots</a>";
    }
}
?>