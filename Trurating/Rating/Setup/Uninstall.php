<?php

namespace Trurating\Rating\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Module uninstall code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $connection = $setup->getConnection();
		
		$connection->delete(
			$setup->getTable('adminnotification_inbox'),
			['title = ?' => 'Complete Integrating TruRating Online']
		);
       
		
        //$connection->dropTable($connection->getTableName('your_table_name_here'));
        $setup->endSetup();
    }
}