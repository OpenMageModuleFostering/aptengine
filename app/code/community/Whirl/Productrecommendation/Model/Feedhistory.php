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

class Whirl_Productrecommendation_Model_Feedhistory extends Mage_Core_Model_Abstract
{
	const ORDER_JOB_CODE 	= "wd_feed_order_associated";
	const VIEW_JOB_CODE 	= "wd_feed_product_refered";
	const ORDER_JOB_NAME 	= "orders";
	const VIEW_JOB_NAME 	= "views";
	
    protected function _construct()
    {
        $this->_init('productrecommendation/feedhistory');
    }
    
    /**
     * Return job code from Constant job => name code map.
     * 
     * @param string $jobName
     */
    protected function getJobCodeForName($jobName) {
    	$jobEnum = array(
    				self::ORDER_JOB_NAME => self::ORDER_JOB_CODE,
    				self::VIEW_JOB_NAME => self::VIEW_JOB_CODE
    			);
    	
    	if(isset($jobEnum[$jobName])) {
    		return $jobEnum[$jobName];
    	}
    	return null;
    }
    
    /**
     * Retrives for the existing job code if not available creates new one.
     *  
     * @param string $jobName
     * @return Whirl_Productrecommendation_Model_Feedhistory |NULL
     */
    public function getJobModel($jobName) {
    	if($this->getJobCodeForName($jobName) != null) {
    		$historyCollection = $this->getCollection()
    			->addFieldToFilter('job_name', $this->getJobCodeForName($jobName));
    		if($historyCollection->count() == 0) {
    			return $this->createJobEntry(self::ORDER_JOB_CODE);
    		}
    		
    		return $historyCollection->getFirstItem();
    	}
    	
    	return null;
    }
    
    /**
     * Creates new entry when job code is not avaialble.
     * @param unknown_type $jobCode
     * @return Whirl_Productrecommendation_Model_Feedhistory
     */
    public function createJobEntry($jobCode) {
    	$data['job_name'] = $jobCode;
    	$this->addData($data);
    	$this->save();
    	return $this;
    }
}
