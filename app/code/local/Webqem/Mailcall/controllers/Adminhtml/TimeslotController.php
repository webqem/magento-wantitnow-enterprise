<?php

class Webqem_Mailcall_Adminhtml_TimeslotController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('webqemmailcall/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('webqemmailcall/timeslot')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			
			Mage::register('timeslot_data', $model);
			if ($id) {
				$collectionTimeslot = Mage::getModel('webqemmailcall/timeslot')->getCollection();
				$collectionTimeslot->getSelect()->where('number_day = '.$model->getNumberDay());
				Mage::register('timeslot_edit_data', $collectionTimeslot);
			}
			$this->loadLayout();
			$this->_setActiveMenu('webqemmailcall/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('webqemmailcall/adminhtml_timeslot_edit'))
				->_addLeft($this->getLayout()->createBlock('webqemmailcall/adminhtml_timeslot_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webqemmailcall')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
	  			
			$timeslotDay = $data['timeslot_day'];
			$segments 	 = $data['segment'];
			$deleteids   = explode(',', trim($data['delete_ids'], ','));
			
			if ($segments==null) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webqemmailcall')->__('Unable to find timeslot to save'));
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
			
			foreach ($segments as $id=>$segment) {
				$model = Mage::getModel('webqemmailcall/timeslot');
				$model->setNumberDay($timeslotDay);
				$model->setDescription($segment['description']);
				$model->setTimeStart($segment['from_time']);
				$model->setTimeEnd($segment['to_time']);
				if (is_numeric($id)) {
					$model->setId($id);
				}
				$model->save();
			}
			foreach ($deleteids as $deleteId) {
				$model = Mage::getModel('webqemmailcall/timeslot');
					
				$model->setId($deleteId)
					->delete();
			}
			
			try {
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('webqemmailcall')->__('Timeslot was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webqemmailcall')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $data = $this->getRequest()->getParams() ) {
			try {
				$model = Mage::getModel('webqemmailcall/timeslot')->load($data['id']);
				
				$collectionTimeslot = Mage::getModel('webqemmailcall/timeslot')->getCollection();
				$collectionTimeslot->getSelect()->where('number_day = '.$model->getNumberDay());
				
				foreach ($collectionTimeslot as $timeslot) {
					$model = Mage::getModel('webqemmailcall/timeslot');
				
					$model->setId($timeslot->getId())
							->delete();
				}
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Timeslot was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $giftIds = $this->getRequest()->getParam('timeslot');
        
        if(!is_array($giftIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($giftIds as $giftId) {
	                $model = Mage::getModel('webqemmailcall/timeslot')->load($giftId);
					
					$collectionTimeslot = Mage::getModel('webqemmailcall/timeslot')->getCollection();
					$collectionTimeslot->getSelect()->where('number_day = '.$model->getNumberDay());
					
					foreach ($collectionTimeslot as $timeslot) {
						$model = Mage::getModel('webqemmailcall/timeslot');
					
						$model->setId($timeslot->getId())
								->delete();
					}
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($giftIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $giftIds = $this->getRequest()->getParam('gift');
        if(!is_array($giftIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($giftIds as $giftId) {
                    $gift = Mage::getSingleton('gift/gift')
                        ->load($giftId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($giftIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}