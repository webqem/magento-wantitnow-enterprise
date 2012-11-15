<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

class Webqem_Mailcall_Block_Adminhtml_System_Config_Form_Field_Apikey extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<div id="apikey_condition_template" style="display:none">';
        $tmpHtml=$this->_getRowTemplateHtml('template');
        $html .= str_replace('[apikey]','[apikey_template]',str_replace('[homeaddress]','[homeaddress_template]', str_replace('[suburb]','[suburb_template]', $tmpHtml)));
        $html .= '</div>';

        $html .= '<ul id="apikey_condition_container">';
        
        if ($this->_getValue('apikey') || $this->_getValue('homeaddress')) {
            foreach ($this->_getValue('apikey') as $row=>$arr) {
                $html .= $this->_getRowTemplateHtml($row);
            }
        }
        $html .= '</ul>';
        $html .= $this->_getAddRowButtonHtml('apikey_condition_container',
            'apikey_condition_template', $this->__('Add New API KEY'));

        return $html;
    }

    protected function _getRowTemplateHtml($r='0')
    {
        $tmp_apikey=$this->_getValue('apikey/'.$r);
        $tmp_suburb=$this->_getValue('suburb/'.$r);
        $tmp_homeaddress=$this->_getValue('homeaddress/'.$r);
        
        $html = '<li style="margin-bottom:10px;">';
        $html .= '<div>';
        $html .= 'API KEY:<input class=" input-text" type="text" value="'.$tmp_apikey.'" name="'.$this->getElement()->getName().'[apikey][]">';
        $html .= '</div>';
        
        $html .= '<div style="margin-top:5px;">';
        $html .= 'Suburb:<input class=" input-text" type="text" value="'.$tmp_suburb.'" name="'.$this->getElement()->getName().'[suburb][]">';
        $html .= '</div>';
        
        $html .= '<div style="margin-top:5px;">';
        $html .= 'From Address:<input class=" input-text" type="text" value="'.$tmp_homeaddress.'" name="'.$this->getElement()->getName().'[homeaddress][]">';
        $html .= '</div>';
        
        $html .= '<div style="margin-top:3px;">';
        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</div>';
        
        $html .= '</li>';

        return $html;
    }
   
    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/'.$key);
    }


    protected function _getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('add '.$this->_getDisabled())
                    ->setLabel($this->__($title))
                    //$this->__('Add')
                    ->setOnClick("Element.insert($('".$container."'), {bottom: $('".$template."').innerHTML.replace('[apikey_template]','[apikey]').replace('[homeaddress_template]','[homeaddress]').replace('[suburb_template]','[suburb]')});")
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
                    ->setOnClick("Element.remove($(this).up('".$selector."'));")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }
}