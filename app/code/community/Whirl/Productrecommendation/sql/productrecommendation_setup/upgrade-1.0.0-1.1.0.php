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
$connection = $installer->getConnection();

/**
 * Create table 'wd_schedule_history'
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('productrecommendation/feedhistory'))
	->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
			'identity'  => true,
			'unsigned'  => true,
			'nullable'  => false,
			'primary'   => true,
		), 'History Id')
	->addColumn('job_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
			'nullable'  => false,
		), 'Job Name')
	->addColumn('previous_executed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
			'nullable'  => true,
		), 'Previously Executed Date')
	->addColumn('executed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
			'nullable'  => false,
		), 'Last Executed Date')
	->addIndex($installer->getIdxName('productrecommendation/feedhistory', array('job_name'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
			array('job_name'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
	->setComment('Feed Table');
	
$installer->getConnection()->createTable($table);
	
