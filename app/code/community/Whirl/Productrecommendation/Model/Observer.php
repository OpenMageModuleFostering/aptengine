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

/**
 * Product Recommendation Observer
 *
 * @category   Whirl
 * @package    Whirl_Productrecommendation
 */
class Whirl_Productrecommendation_Model_Observer
{
    const XML_PATH_ALSO_VIEWED_PRODUCTS_ACTIVE	= "whirl/alsoviewed/active";
    const XML_PATH_PRODUCTS_ORDERED_ACTIVE	 	= "whirl/alsoordered/active";
    const XML_PATH_DEFAULT_PRODUCT_CART		 	= "whirl/cartrecommended/default_sku";

    /**
     * Select queries from feed to insert in map. 
     */
    const SALES_ORDER_SELECT_QUERY 		= "SELECT order_id as map_id, sku FROM sales_flat_order_item WHERE parent_item_id IS NULL";
    const SALES_ORDER_GROUP_BY 			= "GROUP BY sku, order_id";
    const PRODUCT_VIEWED_SELECT_QUERY	= "SELECT session_id as map_id, sku_code as sku FROM wd_feed GROUP BY session_id, sku_code";
    const TRUNCATE_VIEWED_RECORD_QUERY	= "TRUNCATE TABLE wd_feed";
    const SCHEDULER_ORDER_LOG_FILE		= "whirl_scheduler.log";
   
    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = array();
    
    protected function getHelper() {
    	return Mage::helper('productrecommendation');
    }
    
    /**
     * On every product load after creates feed entry
     */
    public function addProductViewedEntry(Varien_Event_Observer $observer) {
    	$currentSku = Mage::registry("current_product")->getSku();
    	$currentSession = Mage::getSingleton('core/session')->getSessionId();
    	$newEntry = Mage::getModel("productrecommendation/wdfeed");
    	$newEntry->setSessionId($currentSession);
    	$newEntry->setSkuCode($currentSku);
    	$newEntry->save();
    }
    
    /**
     * Select query preparations.
     */
    public function prepareSalesOrderQuery() {
    	$feedEntry = Mage::getModel("productrecommendation/feedhistory")
    		->getJobModel(Whirl_Productrecommendation_Model_Feedhistory::ORDER_JOB_NAME);
    	$orderFromDate = $feedEntry->getData('executed_at');
    	$selectQuery = self::SALES_ORDER_SELECT_QUERY;
    	if($orderFromDate != "" || $orderFromDate != null) {
    		$selectQuery = $selectQuery . " AND created_at >= '" . $orderFromDate ."'";
    	}
    	
    	$selectQuery = $selectQuery . " " . self::SALES_ORDER_GROUP_BY;
    	$feedEntry->setData('previous_executed_at', $orderFromDate);
    	$feedEntry->setData('executed_at', Mage::getModel('core/date')->timestamp(time()));
    	$feedEntry->save();
    	return $selectQuery;
    }
    
    
    /**
     * Prepares entry from feed to product refered count. 
     */
    public function populateProductReferedCount() {
    	if (!Mage::getStoreConfigFlag(self::XML_PATH_ALSO_VIEWED_PRODUCTS_ACTIVE)) {
    	 return $this;
    	}
    	
    	Mage::log("Product Scheduler Started.");
		$associateProductArr = $this->getHelper()->getMapEntries(self::PRODUCT_VIEWED_SELECT_QUERY);
    	$this->createMapEntry($associateProductArr, "views");
    	$this->getHelper()->getWriteAdapter()->query(self::TRUNCATE_VIEWED_RECORD_QUERY);
    	Mage::log("Product Scheduler Completed.");
    	return $this;
    }
    
    
    /**
     * Prepares product associated count from flat order.
     */
    public function populateOrderAssociatedCount()
    {
    	if (!Mage::getStoreConfigFlag(self::XML_PATH_PRODUCTS_ORDERED_ACTIVE)) {
    		return $this;
    	}
    	
    	Mage::log("Order Scheduler Started.", null, self::SCHEDULER_ORDER_LOG_FILE);
    	$associateProductArr = $this->getHelper()->getMapEntries($this->prepareSalesOrderQuery());
    	if(empty($associateProductArr)) {
    		Mage::log("Order Scheduler Completed. No records found.", null, self::SCHEDULER_ORDER_LOG_FILE);
    	} else {
    		Mage::log(array_keys($associateProductArr), null, self::SCHEDULER_ORDER_LOG_FILE);
    		$this->createMapEntry($associateProductArr, "orders");
    		Mage::log("Order Scheduler Completed.", null, self::SCHEDULER_ORDER_LOG_FILE);
    	}
    	
    	return $this;
    }

    
    /**
     * Insert or Updates the associate product Map entry from the array.
     * 
     * @param array $associateProductArr
     */
    public function createMapEntry($associateProductArr, $modelType) {
    	foreach($associateProductArr as $orders) {
    		if(count($orders) > 1) {
    			$insertValues = "";
    			$updateQuery = "";
    			foreach($orders as $skuCode => $associateArray) {
    				foreach($associateArray as $associateCode) {
    					$count = 1;
    					$modelName = "productrecommendation/". $modelType;
    					$tableName = "wd_".$modelType."_map";
    					$mapModel = Mage::getModel($modelName);
    					$orderMapResult = $mapModel->getMapByGroup($skuCode, $associateCode);
    					if(isset($orderMapResult) && $orderMapResult != null) {
    						$count = $count + $orderMapResult['count'];
    						$updateQuery = $updateQuery . "UPDATE $tableName SET count = " . $count;
    						$updateQuery = $updateQuery . " WHERE map_id=".$orderMapResult['map_id'].";";
    					} else {
    						$insertValues = $insertValues . "(";
    						$insertValues = $insertValues . "'".$skuCode ."','". $associateCode ."', 1"; 
    						$insertValues = $insertValues . "),";
    					}
    				}
    			}
    			
    			if($updateQuery != "") 
    				$this->getHelper()->getWriteAdapter()->query($updateQuery);
    			
    			$this->getHelper()->insertMapEntries($modelType, $insertValues);
    		}
    	}
    }
}
