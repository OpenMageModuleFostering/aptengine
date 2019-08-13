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
 *  People also viewed Collection model resource
 */
class Whirl_Productrecommendation_Model_Resource_Views_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	const XML_PATH_PRODUCTS_COUNT = "";
	
    /**
     * Initialize collection
     *
     */
    public function _construct()
    {
        $this->_init('productrecommendation/views');
    }
    
    /**
     * Fetchs the view map collection.
     * 
     * @param array $skuCode
     */
    public function loadPeopleViewedProducts($skuCode)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('productrecommendation/views'), array('also_viewed_sku_code'))
            ->where('sku_code = :current_sku_code')
            ->order('count DESC')->limit(5);
        $viewedProducts = $this->getConnection()->fetchCol($select, array(':current_sku_code' => $skuCode));
        return $viewedProducts;
    }
}
