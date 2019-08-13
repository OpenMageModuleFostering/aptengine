<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Whirl
 * @package     Whirl_Productrecommendation
 */

class Whirl_Productrecommendation_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const XML_PATH_ORDER_DAYS			= 'system/backup/maintenance';
	
	/**
	 * Insert queries for map entry.
	 */
	const ORDERED_PRODUCT_INSERT_QUERY	= "INSERT INTO wd_orders_map (sku_code, associated_sku_code, count) VALUES";
	const PRODUCT_VIEWED_INSERT_QUERY	= "INSERT INTO wd_views_map (sku_code, also_viewed_sku_code, count) VALUES";
	
	/**
	 * Retrieve the Core resource
	 *
	 * @return
	 */
	public function getCoreResource() {
		return Mage::getSingleton('core/resource');
	}
	
	/**
	 * Retrieve the read DB adapter
	 *
	 * @return
	 */
	public function getReadAdapter() {
		return $this->getCoreResource()->getConnection("core_read");
	}
	
	/**
	 * Retrieve the write DB adapter
	 *
	 * @return
	 */
	public function getWriteAdapter()
	{
		return $this->getCoreResource()->getConnection('core_write');
	}
	
	protected function getInsertQuery($modelType) {
		switch($modelType) {
			case "views":
				return self::PRODUCT_VIEWED_INSERT_QUERY;
			case "orders":
				return self::ORDERED_PRODUCT_INSERT_QUERY;
		}
	}
	
	/**
	 * Fetch data from the query and converts to insertable array.
	 * 
	 * @param queryString $recordQuery
	 */
	public function getMapEntries($recordQuery) {
		try {
			$feedAssociateResult = $this->getReadAdapter()->fetchAll($recordQuery);
			$associateProductArr = $this->buildInsertArray($feedAssociateResult);
			return $associateProductArr;
		}
    	catch (Exception $e) {
    		$this->logError($e);
    	}
    	
    	return null;
	}
	
	/**
	 * Creates map entries for the query.
	 * 
	 * @param queryString $insertQuery
	 * @param queryString $insertValues
	 */
	public function insertMapEntries($modelType, $insertValues) {
		if($insertValues != "") {
			try {
				$insertQuery = $this->getInsertQuery($modelType) . " " . $insertValues;
				$this->getWriteAdapter()->query(substr(trim($insertQuery),0,-1));
			} catch(Exception $e) {
				$this->logError($e);
			}
		}
	}
	
	/**
	 * Generates the associate array from result set of order items.
	 * 
	 * @param array $resultSet
	 */
	public function buildInsertArray($resultSet) {
		$assoicateProducts = array();
		if(!empty($resultSet)) {
			foreach($resultSet as $associateArr) {
				if(isset($assoicateProducts[$associateArr['map_id']])){
					foreach($assoicateProducts[$associateArr['map_id']] as $key => $eachSku) {
						$assoicateProducts[$associateArr['map_id']][$associateArr['sku']][] = $key;
						$assoicateProducts[$associateArr['map_id']][$key][] = $associateArr['sku'];
					}
				} else {
					$assoicateProducts[$associateArr['map_id']][$associateArr['sku']] = array();
				}
			}
		}
		
		return $assoicateProducts; 
	}
	
	/**
	 * Retrives the child product id from parent ids.
	 * 
	 * @param int $parentId
	 */
	public function getChildProducts($parentId) {
		$select = $this->getReadAdapter()->select()
				->from($this->getCoreResource()->getTableName("catalog/product_relation"), array("child_id"))
				->where("parent_id = ?", $parentId);
		return $this->getReadAdapter()->fetchCol($select);
	}
	
	/**
	 * Cart recommended data preparation
	 * @param $inputSku
	 */
	public function getCartRecommedData($inputSku) {
		$sql = "SELECT sku_code,associated_sku_code, count FROM wd_orders_map where sku_code like '" . $inputSku . "'";
		$result = $this->getReadAdapter()->query($sql);
		$data = array();
		while ($row = $result->fetch()) {
			$data [] = $row;
		}
		
		return $data;
	}
	
	
	/**
	 * Logs the error
	 */
	public function logError($e) {
		Mage::log($e->getMessage(), Zend_Log::ERR);
		Mage::logException($e);
	}
}