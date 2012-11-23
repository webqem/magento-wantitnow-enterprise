<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Block_Adminhtml_System_Config_Form_Field_Dayandhourstimeslots extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<input type="hidden" value="'.count($this->_getValue('allowday')).'" id="dayandhours_timeslots_condition_num"><div id="dayandhours_timeslots_condition_template" style="display:none">';
        $tmpHtml=$this->_getRowTemplateHtml();
        $html .= str_replace('[allowhourto]','[allowhourto_template]',str_replace('[allowhourfrom]','[allowhourfrom_template]',str_replace('[allowday]', '[allowday_template]', $tmpHtml)));
        $html .= '</div>';

        $html .= '<ul id="dayandhours_timeslots_condition_container">';
        
        if ($this->_getValue('allowday') || $this->_getValue('allowhour')) {
            foreach ($this->_getValue('allowday') as $row=>$arr) {
                $html .= $this->_getRowTemplateHtml($row);
            }
        }
        $html .= '</ul>';
        $html .= $this->_getAddRowButtonHtml('dayandhours_timeslots_condition_container',
            'dayandhours_timeslots_condition_template', $this->__('Add Condition'));

        return $html;
    }

    protected function _getRowTemplateHtml($r='r0')
    {
        $mailcallCarrierModel=Mage::getModel('webqemmailcall/carrier_timeslots');
        $_locale=$mailcallCarrierModel->getNowSystemTime();
        $html = '<li style="margin-bottom:10px;">';
        $html .= '<div style="width:135px;margin-right:10px;float:left;">';
        $html .= '<select name="'.$this->getElement()->getName().'[allowday]['.$r.'][]" '.$this->_getDisabled().' class="select multiselect" multiple="multiple" size="6" style="width:135px;">';
        foreach ($this->getWeekdays() as $val) {
              $html .= '<option value="'.$val['value'].'" '.$this->_getSelected('allowday/'.$r, $val['value']).'>'.$val['label'].'</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div style="width:125px;float:left;height:125px;padding: 4px; border:1px #ccc solid;">';
        $html .= 'From:<br /><select name="'.$this->getElement()->getName().'[allowhourfrom]['.$r.'][]" '.$this->_getDisabled().' class="select" style="width:120px;">';
        foreach ($this->getHours() as $key=>$val) {
              $html .= '<option value="'.$key.'" '.$this->_getSelected('allowhourfrom/'.$r, $key).'>'.$val.'</option>';
        }
        $html .= '</select><br />';
        
        $html .= 'To:<br /><select name="'.$this->getElement()->getName().'[allowhourto]['.$r.'][]" '.$this->_getDisabled().' class="select" style="width:120px;">';
        foreach ($this->getHours() as $key=>$val) {
              $html .= '<option value="'.$key.'" '.$this->_getSelected('allowhourto/'.$r, $key).'>'.$val.'</option>';
        }
        $html .= '</select><br /><br />';
        $html .= '<font color="green">Current system time:<br />'.date('H:i',$_locale).'</font>';
        $html .= '</div>';
        
        $html .= '<div style="margin-top:3px;">';
        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</div>';
        
        $html .= '</li>';

        return $html;
    }
    protected function getHours(){
        $_hours=array();
        $_minute=array('00','30');
        for($i=0;$i<24;$i++){
            for($j=0;$j<=1;$j++){
                $hh=str_pad($i,2,"0",STR_PAD_LEFT);
                $mm=$_minute[$j];
                $_hours[$hh.'.'.$mm]=$hh.':'.$mm;
            }
        }
        return $_hours;
    }
    protected function getWeekdays(){
        return Mage::getModel('adminhtml/system_config_source_locale_weekdays')->toOptionArray();
    }

    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/'.$key);
    }

    protected function _getSelected($key, $value)
    {
        if(is_array($this->_getValue($key))){
            return in_array($value,$this->_getValue($key)) ? 'selected="selected"' : '';
        }else{
            return '';
        }
    }

    protected function _getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('add '.$this->_getDisabled())
                    ->setLabel($this->__($title))
                    //$this->__('Add')
                    ->setOnClick("Element.insert($('".$container."'), {bottom: $('".$template."').innerHTML.replace('[allowday_template][r0]','[allowday][r'+$('dayandhours_timeslots_condition_num').value+']').replace('[allowhourfrom_template][r0]','[allowhourfrom][r'+$('dayandhours_timeslots_condition_num').value+']').replace('[allowhourto_template][r0]','[allowhourto][r'+$('dayandhours_timeslots_condition_num').value+']')});$('dayandhours_timeslots_condition_num').value=($('dayandhours_timeslots_condition_num').value*1)+1;")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    protected function _getRemoveRowButtonHtml($selector='li', $title='Remove')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('delete v-middle '.$this->_getDisabled())
                    ->setLabel($this->__($title))
                    //$this->__('Remove')
                    ->setOnClick("Element.remove($(this).up('".$selector."'));if($('dayandhours_timeslots_condition_num').value>0){ $('dayandhours_timeslots_condition_num').value=$('dayandhours_timeslots_condition_num').value-1;}")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }
}