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
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'wd_feed'
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('productrecommendation/wdfeed'))
	->addColumn('feed_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
	), 'Feed Id')
	->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
	), 'Session Id')
	->addColumn('sku_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'unsigned'  => true,
		'nullable'  => false,
	), 'Current Sku Code')
	->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable'  => false,
		'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
	), 'Created Date')
	->setComment('Feed Table');

$installer->getConnection()->createTable($table);


/**
 * Create table 'wd_views_map'
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('productrecommendation/views'))
	->addColumn('map_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
	), 'Map Id')
	->addColumn('sku_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'unsigned'  => true,
		'nullable'  => false,
	), 'Sku Code')
	->addColumn('also_viewed_sku_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'unsigned'  => true,
		'nullable'  => false,
	), 'Refered Sku Code')
	->addColumn('count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
	), 'Count')
	->setComment('Product Views Map Table');

$installer->getConnection()->createTable($table);

/**
 * Create table 'wd_orders_map'
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('productrecommendation/orders'))
	->addColumn('map_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
	), 'Map Id')
	->addColumn('sku_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'unsigned'  => true,
		'nullable'  => false,
	), 'Sku Code')
	->addColumn('associated_sku_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'unsigned'  => true,
		'nullable'  => false,
	), 'Associated Sku Code')
	->addColumn('count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
	), 'Count')
	->setComment('Ordered Map Table');

$installer->getConnection()->createTable($table);
