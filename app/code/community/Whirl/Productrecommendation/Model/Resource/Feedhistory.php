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

class Whirl_Productrecommendation_Model_Resource_Feedhistory extends Mage_Core_Model_Resource_Db_Abstract
{
	/**
	 * Initialize resource
	 *
	 */
	protected function _construct()
	{
		$this->_init('productrecommendation/feedhistory', 'history_id');
	}

}
