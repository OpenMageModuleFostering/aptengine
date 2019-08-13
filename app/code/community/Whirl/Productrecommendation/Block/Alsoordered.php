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

class Whirl_Productrecommendation_Block_Alsoordered extends Mage_Catalog_Block_Product_List
{
	const XML_PATH_PRODUCTS_ORDERED_TITLE		= "whirl/alsoordered/title";
	const XML_PATH_PRODUCTS_ORDERED_ACTIVE	 	= "whirl/alsoordered/active";
	const XML_PATH_ORDERED_REQUIRED_PRODUCTS	= "whirl/alsoordered/requiredproducts";
	const XML_PATH_ORDERED_PRODUCTS_COUNT		= "whirl/alsoordered/count";

	const PRODUCT_TYPE_CONFIGURABLE 			= "configurable";
	const PRODUCT_TYPE_GROUPED 					= "grouped";
	const PRODUCT_TYPE_BUNDELED 				= "configurable";

	protected $_productCollection;
	protected $_currentSimpleSkus;
	protected $_addToCartSimpleSku;
	
	/**
	 * Price template
	 *
	 * @var string
	 */
	protected $_priceBlockDefaultTemplate = 'productrecommendation/price.phtml';

	public function getBlockTitle() {
		return Mage::getStoreConfig(self::XML_PATH_PRODUCTS_ORDERED_TITLE);
	}

	public function getColumnCount() {
		return Mage::getStoreConfig(self::XML_PATH_ORDERED_PRODUCTS_COUNT);
	}
	
	public function canShowRequired() {
		return Mage::getStoreConfig(self::XML_PATH_ORDERED_REQUIRED_PRODUCTS);
	}
	
	public function getCurrentSimpleSku() {
		return $this->_addToCartSimpleSku;
	}
	
	public function getCurrencySym() {
		$currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		return Mage::app()->getLocale()->currency($currencyCode)->getSymbol();
	}

	protected function getPrimaryProductUrl() {
		foreach($this->_productCollection as $relatedProduct) {
			if(in_array($relatedProduct->getSku(), $this->_currentSimpleSkus)) {
				$this->_addToCartSimpleSku = $relatedProduct->getSku();
				return Mage::helper('checkout/cart')->getAddUrl($relatedProduct);
			}
		}
	}	
	
	protected function _getPriceBlockTemplate($productTypeId)
	{
		if (isset($this->_priceBlockTypes[$productTypeId])) {
			if ($this->_priceBlockTypes[$productTypeId]['template'] != '') {
				return $this->_priceBlockTypes[$productTypeId]['template'];
			}
		}
		return $this->_priceBlockDefaultTemplate;
	}

	/**
	 * Prepares the add all item to cart Url. Creates url based on the related product url concept. Can add only one quantity
	 */
	public function getAddAllItemsUrl() {
		$addToCartUrl = $this->getPrimaryProductUrl(); 
		$alsoOrderedUrl = "related_product/";
		foreach($this->_productCollection as $alsoOrdered) {
			if($this->getCurrentSimpleSku() != $alsoOrdered->getSku() && 
					!$alsoOrdered->getTypeInstance(true)->hasRequiredOptions($alsoOrdered)) {
				$alsoOrderedUrl = $alsoOrderedUrl . $alsoOrdered->getId() . ",";
			}
		}

		return $addToCartUrl . $alsoOrderedUrl;
	}

	/**
	 * Returns the product collection to list view.
	 */
	public function getAlsoOrderedProducts() {
		if(Mage::getStoreConfig(self::XML_PATH_PRODUCTS_ORDERED_ACTIVE)) {
			$currentProduct = Mage::registry("current_product");
			$currentSimpleProductSku = $currentProduct->getSku();
			$this->_currentSimpleSkus = array($currentSimpleProductSku); 
			if($currentProduct->getTypeId() == self::PRODUCT_TYPE_CONFIGURABLE ||
					$currentProduct->getTypeId() == self::PRODUCT_TYPE_GROUPED ||
					$currentProduct->getTypeId() == self::PRODUCT_TYPE_BUNDELED	) {
				$currentSimpleProductSku = $this->getRelatedSimpleSkus($currentProduct->getId());
			}
			
			return $this->fetchOrderedProducts($currentSimpleProductSku);
		}
		
		return null;
	}

	/**
	 * Fetch all related products from the current product id.
	 *
	 * @param unknown_type $productId
	 */
	protected function getRelatedSimpleSkus($productId) {
		$simpleProductIds = Mage::helper("productrecommendation")->getChildProducts($productId);
		$skuCollection = Mage::getModel("catalog/product")->getCollection()
		->addAttributeToFilter("type_id", "simple")
		->addAttributeToFilter("entity_id", array("in" => $simpleProductIds));
		$childSkus = array();
		foreach ($skuCollection as $skuData) {
			$childSkus[] = $skuData->getSku();
		}
		
		$this->_currentSimpleSkus = $childSkus; 
		return $childSkus;
	}

	/**
	 * Use to retrive the simple product sku for configure.
	 *
	 * @param unknown_type $productId
	 * @return multitype:NULL
	 */
	protected function getConfigurableSimpleSkus($productId) {
		$simpleProductIds = Mage::getModel('catalog/product_type_configurable')
		->getChildrenIds($productId);
		$skuCollection = Mage::getModel("catalog/product")->getCollection()
		->addAttributeToFilter("entity_id", array("in" => $simpleProductIds[0]));
		$childSkus = array();
		foreach ($skuCollection as $skuData) {
			$childSkus[] = $skuData->getSku();
		}

		return $childSkus;
	}

	/**
	 * Pull the products placed along the current product from map table.
	 *
	 * @param unknown_type $skuCode
	 */
	public function fetchOrderedProducts($skuCode) {
		$orderedSkuCodes = Mage::getResourceModel("productrecommendation/orders_collection")->loadPeopleAlsoOrdered($skuCode);
		if(count($orderedSkuCodes) > 0 ) {
			$orderedSkuCodes = array_unique($orderedSkuCodes);
			
			$orderString = array('CASE e.entity_id');
			foreach($productIds as $i => $productId) {
				$orderString[] = 'WHEN '.$productId.' THEN '.$i;
			}
			$orderString[] = 'END';
			$orderString = implode(' ', $orderString);
			
			$collection = Mage::getResourceModel('catalog/product_collection');
			$this->prepareProductCollection($collection);
			$collection->addAttributeToFilter('sku', array('in' => $orderedSkuCodes))->addStoreFilter();
			$collection->getSelect()->order(new Zend_Db_Expr('FIELD(sku, "' . implode('","', $orderedSkuCodes) . '") ASC'));
			$this->_productCollection = $collection;
			return $collection;
		}

		return null;
	}

	/**
	 * Initialize product collection overwritten to display simple products
	 */
	public function prepareProductCollection($collection)
	{
		$collection
		->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
		->addMinimalPrice()
		->addFinalPrice()
		->addTaxPercents();
		// 		->addUrlRewrite($this->getCurrentCategory()->getId());

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		return $this;
	}
}