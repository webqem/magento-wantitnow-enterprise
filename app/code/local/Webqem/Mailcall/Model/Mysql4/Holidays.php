<?php

class Webqem_Mailcall_Model_Mysql4_Holidays extends Mage_Core_Model_Mysql4_Abstract
{
	/**
	 * Count of imported table rates
	 *
	 * @var int
	 */
	protected $_importedRows        = 0;
	protected $_importErrors;
	
    public function _construct()
    {
        $this->_init('webqemmailcall/holidays', 'id');
    }
    public function uploadAndImport(Varien_Object $object)
    {
    	if (empty($_FILES['groups']['tmp_name']['timeslot']['fields']['import']['value'])) {
    		return $this;
    	}
    	
    	$csvFile = $_FILES['groups']['tmp_name']['timeslot']['fields']['import']['value'];
    	$website = Mage::app()->getWebsite($object->getScopeId());
    	
    	$io     = new Varien_Io_File();
    	$info   = pathinfo($csvFile);
    	
    	$io->open(array('path' => $info['dirname']));
    	$io->streamOpen($info['basename'], 'r');
    	
    	// check and skip headers
    	$states = $io->streamReadCsv();
		array_shift($states);
		
    	$adapter = $this->_getWriteAdapter();
    	$adapter->beginTransaction();
    	
    	try {
    		$rowNumber  = 1;
    		$importData = array();
    	
    		$adapter->delete($this->getMainTable());
    	
    		while (false !== ($csvLine = $io->streamReadCsv())) {
    			$rowNumber ++;
    			
    			
    			if (empty($csvLine) || empty($csvLine[0])) {
    				continue;
    			}
    			$date = array_shift($csvLine);
    			for ($i=0; $i<count($states); $i++) {
    				$importData[] = array($date,$states[$i], $csvLine[$i]);
    			}
    			
    			if (count($importData) == 5000) {
    				$this->_saveImportData($importData);
    				$importData = array();
    			} 
    		}
    		
    		$this->_saveImportData($importData);
    		$io->streamClose();
    	} catch (Mage_Core_Exception $e) {
    		$adapter->rollback();
    		$io->streamClose();
    		Mage::throwException($e->getMessage());
    	} catch (Exception $e) {
    		$adapter->rollback();
    		$io->streamClose();
    		Mage::logException($e);
    		Mage::throwException(Mage::helper('shipping')->__('An error occurred while import table rates.'));
    	}
    	
    	$adapter->commit();
    	
    	if ($this->_importErrors) {
    		$error = Mage::helper('shipping')->__('File has not been imported. See the following list of errors: %s', implode(" \n", $this->_importErrors));
    		Mage::throwException($error);
    	}
    	return $this;
    }
    /**
     * Save import data batch
     *
     * @param array $data
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate
     */
    protected function _saveImportData(array $data)
    {
    	if (!empty($data)) {
    		$columns = array('holidays_date', 'holidays_state', 'holidays_status');
    		$this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
    		$this->_importedRows += count($data);
    	}
    
    	return $this;
    }
}