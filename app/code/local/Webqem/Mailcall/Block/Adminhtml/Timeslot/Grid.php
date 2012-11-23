<?php

class Webqem_Mailcall_Block_Adminhtml_Timeslot_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('webqemmailcallGrid');
      $this->setDefaultSort('timeslot_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('webqemmailcall/timeslot')->getCollection();
      $collection->getSelect()->columns('COUNT(description) AS quality')
      							->group('number_day');
      
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      
      $this->addColumn('number_day', array(
      		'header'    => Mage::helper('webqemmailcall')->__('Day'),
      		'align'     =>'left',
      		'index'     => 'number_day',
      		'renderer'	=> 'Webqem_Mailcall_Block_Adminhtml_Timeslot_Renderer_Day'
      ));
      $this->addColumn('quality', array(
          'header'    => Mage::helper('webqemmailcall')->__('Number Timeslot'),
          'align'     =>'left',
          'index'     => 'quality',
      ));
     
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('webqemmailcall')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('webqemmailcall')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    ),
                		array(
                				'caption'   => Mage::helper('webqemmailcall')->__('Delete'),
                				'url'       => array('base'=> '*/*/delete'),
                				'field'     => 'id'
                		)
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
	
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('timeslot_id');
        $this->getMassactionBlock()->setFormFieldName('timeslot');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('webqemmailcall')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('webqemmailcall')->__('Are you sure?')
        ));

        
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}