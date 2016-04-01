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
 * @category   Mage
 * @package    Mage_SecureHosting
 */


$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('securehosting/api_debug');

if (!$installer->getConnection()->isTableExists($tableName)) {

    $table = $installer->getConnection()
        ->newTable($tableName)
        ->addColumn('debug_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Debug Id')

        ->addColumn('debug_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
            ), 'Entry Dt')
        ->addColumn('request_body', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
            'nullable'  => false,
            ), 'request body')
        ->addColumn('response_body', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
            'nullable'  => false,
            ), 'response body')
        ->addIndex($installer->getIdxName('securehosting/api_debug', array('debug_at')),
            array('debug_at'))
        ->setComment('Paygate SecureHosting module debug data');
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();