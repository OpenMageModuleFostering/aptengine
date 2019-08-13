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
* People also viewed block
*/

class Whirl_Productrecommendation_Block_Alsoviewed extends Mage_Catalog_Block_Product_List
{
	const XML_PATH_PRODUCTS_COUNT 				= "whirl/alsoviewed/count";
	const XML_PATH_PRODUCTS_VIEWED_TITLE		= "whirl/alsoviewed/title";
	const XML_PATH_ALSO_VIEWED_PRODUCTS_ACTIVE	= "whirl/alsoviewed/active";
	
	public function getBlockTitle() {
		return Mage::getStoreConfig(self::XML_PATH_PRODUCTS_VIEWED_TITLE);
	}
	
	public function getColumnCount() {
		return Mage::getStoreConfig(self::XML_PATH_PRODUCTS_COUNT);
	}
	
	public function getAddToCartTitle($product) {
		if ($product->getTypeInstance(true)->hasRequiredOptions($product)) {
			return "View Product";
		}
		
		return "Add To Cart";
	}
	
	/**
	 * Returns the product collection to list.
	 */
	public function getViewedProducts() {
		if(Mage::getStoreConfig(self::XML_PATH_ALSO_VIEWED_PRODUCTS_ACTIVE)) {
			$currentProduct = Mage::registry("current_product");
			return $this->fetchViewedProducts($currentProduct->getSku());
		}
		
		return null;
	}
	
	/**
	 * Retrive the products People also viewed from map table.
	 * 
	 * @param String $skuCode
	 */
	public function fetchViewedProducts($skuCode) {
		$viewedSkuCodes = Mage::getResourceModel("productrecommendation/views_collection")->loadPeopleViewedProducts($skuCode);
		if(count($viewedSkuCodes) > 0 ) {
			$collection = Mage::getResourceModel('catalog/product_collection');
			Mage::getModel('catalog/layer')->prepareProductCollection($collection);
			$collection->addAttributeToFilter('sku', array('in' => $viewedSkuCodes))->addStoreFilter();
			$collection->getSelect()->limit($this->getColumnCount());
			return $collection;
		}
		
		return null;
	}
}