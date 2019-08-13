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

class Whirl_Productrecommendation_Model_Orders extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('productrecommendation/orders');
    }
    
    /**
     * Retrives the exising list of map record based on sku and associate sku.
     * 
     * @param string $skuCode
     * @param string $associateCode
     */
    public function getMapByGroup($skuCode, $associateCode) {
    	$helper = Mage::helper('productrecommendation');
    	$select = $helper->getReadAdapter()->select()
            ->from('wd_orders_map')
	    	->where('sku_code = :sku_code')
	    	->where('associated_sku_code = :associated_sku_code');
    	
    	$bind = array(':sku_code' => $skuCode, ':associated_sku_code' => $associateCode);
    	$orderMapId = $helper->getReadAdapter()->fetchRow($select, $bind);
    	if(!empty($orderMapId)) {
    		return $orderMapId;
    	}
    	
    	return null;
    }
}
