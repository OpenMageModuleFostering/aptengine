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

class Whirl_Productrecommendation_Block_Cartrecommended extends Whirl_Productrecommendation_Block_Alsoviewed
{
	const XML_PATH_PRODUCTS_COUNT 				= "whirl/cartrecommended/count";
	const XML_PATH_PRODUCTS_VIEWED_TITLE		= "whirl/cartrecommended/title";
	const XML_PATH_ALSO_VIEWED_PRODUCTS_ACTIVE	= "whirl/cartrecommended/active";

	const PRODUCT_TYPE_CONFIGURABLE 			= "configurable";
	const PRODUCT_TYPE_GROUPED 					= "grouped";
	const PRODUCT_TYPE_BUNDELED 				= "configurable";

	public function getBlockTitle() {
		return Mage::getStoreConfig(self::XML_PATH_PRODUCTS_VIEWED_TITLE);
	}

	public function getColumnCount() {
		return Mage::getStoreConfig(self::XML_PATH_PRODUCTS_COUNT);
	}

	/**
	 * Returns the product collection to list.
	 */
	public function getViewedProducts() {
			$items = Mage::getSingleton('checkout/cart')->getQuote()->getAllVisibleItems();
		if(Mage::getStoreConfig(self::XML_PATH_ALSO_VIEWED_PRODUCTS_ACTIVE)) {
			foreach($items as $item) {
				$recommedProducts[] = $item->getSku();
			}
				
			$recommedSkus = $this->getAssociatedSkus($recommedProducts);
			$collection = Mage::getResourceModel('catalog/product_collection');
			$this->prepareProductCollection($collection);
			$collection->addAttributeToFilter('sku', array('in' => $recommedSkus))->addStoreFilter();
			$collection->getSelect()->limit($this->getColumnCount());
			return $collection;
		}
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
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		return $this;
	}


	/**
	 * Muthu core logic
	 * getAssociatedSkus() will get the associated skus then sort it
	 */
	function getAssociatedSkus(array $inputSkus) {
		$data[] = "";
		foreach($inputSkus as $inputSku) {
			$currentAssociation = Mage::helper('productrecommendation')->getCartRecommedData($inputSku);
			$data = array_merge($data, $currentAssociation);
		}

		$data = array_diff_key($data, array(""));
		$newArray = $this->sortAssociatedSkus($data, $inputSkus);
		$newArray = $this->findAssociations($inputSkus, $newArray);
		return array_keys($newArray);
	}

	/**
	 * findAssociations will find the associations and remove the current sku from the list
	 */
	private function findAssociations($inputSkus, $newArray) {
		$associations = 0;
		foreach ( $inputSkus as $inputSku ) {
			if (isset($newArray [$inputSku])) {
				$associations ++;
				unset($newArray[$inputSku]);
			}
		}
		/*echo "<hr>Total assocations: " . $associations . "<br>";
		 if ($associations > 0)
			echo ("Total asscoiations probability: " . $associations / count ( $inputSkus ) . "<br>");
		else
			echo "No associations found<br>";
		echo '<hr>';*/
		return $newArray;
	}


	/**
	 * sortAssociatedSkus will sort the array by the count in descending order
	 */
	private function sortAssociatedSkus($data, $inputSkus) {
		$newArray = array ();
		$associationArray = array ();
		$associationArray = array ();
		foreach ( $data as $originalData ) {
			$currentSku = $originalData ['associated_sku_code'];
			$currentCount = $originalData ['count'];
			if (! isset ( $associationArray [$currentSku] )) {
				$associationArray [$currentSku] ['associationCount'] = 1;
			}
			if (isset ( $newArray [$currentSku] )) {
				$associationArray [$currentSku] ['associationCount'] ++;
				// if ($newArray [$currentSku] < $currentCount)
				// $newArray [$currentSku] = $currentCount;
				$newArray [$currentSku] = ($newArray [$currentSku] + $currentCount);
			} else
				$newArray [$currentSku] = $currentCount;
			$factor = $associationArray [$currentSku] ['associationCount'] / count ( $inputSkus );
			$associationArray [$currentSku] ['adjustedOrdersCount'] = $newArray [$currentSku] * $factor;
			$associationArray [$currentSku] ['confidence'] = $associationArray [$currentSku] ['associationCount'] / count ( $inputSkus );
		}
		arsort ( $newArray );
		foreach ( $associationArray as $key => $row ) {
			$associationCount [$key] = $row ['associationCount'];
			$adjustedOrdersCount [$key] = $row ['adjustedOrdersCount'];
			$confidence [$key] = $row ['confidence'];
		}
		array_multisort ( $adjustedOrdersCount, SORT_DESC, $associationCount, SORT_DESC, $associationArray );
		return $newArray;
	}
}
