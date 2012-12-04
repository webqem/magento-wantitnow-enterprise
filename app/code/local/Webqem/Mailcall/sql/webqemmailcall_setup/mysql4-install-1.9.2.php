<?php
/**
 * Webqem Mailcall
 *
 * @category    Webqem
 * @package     Webqem_Mailcall
 * @copyright   Copyright (c) 2012 webqem (http://www.webqem.com)
 * @author      webqem
 */

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->run("
		-- DROP TABLE IF EXISTS {$this->getTable('webqem_timeslot')};
		CREATE TABLE {$this->getTable('webqem_timeslot')} (
		`timeslot_id` int(11) unsigned NOT NULL auto_increment,
		`number_day` int(11) NOT NULL default '0',
		`description` varchar(255) NOT NULL default '',
		`time_start` varchar(255) NOT NULL default '',
		`time_end` varchar(255) NOT NULL default '',
		PRIMARY KEY (`timeslot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->run("
		CREATE TABLE IF NOT EXISTS {$this->getTable('webqem_shipping_pickup')} (
`id` int(11) unsigned NOT NULL auto_increment,
`order_id` int(11) NOT NULL,
`timeslot` int(11) NOT NULL default '0',
`timeslot_date` varchar(255) NOT NULL default '',
`sms_dispatched` smallint(6) NOT NULL default '0',
`sms_time_away` smallint(6) NOT NULL default '0',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->run("
		CREATE TABLE IF NOT EXISTS {$this->getTable('webqem_holidays')} (
		`id` int(11) unsigned NOT NULL auto_increment,
		`holidays_date` varchar(255) NOT NULL default '',
		`holidays_state` varchar(255) NOT NULL default '',
		`holidays_status` smallint(6) NOT NULL default '0',
		PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->run("
		CREATE TABLE IF NOT EXISTS {$this->getTable('webqem_request')} (
		`id` int(11) unsigned NOT NULL auto_increment,
		`order_id` varchar(255) NOT NULL default '',
		`shipping_method` varchar(255) NOT NULL default '',
		`request` text NOT NULL default '',
		`status` smallint(6) NOT NULL default '0',
		PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"); 
$attributeId = $installer->getAttributeId('catalog_product', 'wantitnow');
if(!$attributeId && false){
    $defaultValue = '1';
    $installer->addAttribute('catalog_product', 'wantitnow', array(
        'group'             => 'General',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Can ship with Want It Now',
        'input'             => 'boolean',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => true,
        'user_defined'      => true,
        'default'           => $defaultValue,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'visible_in_advanced_search' => false,
        'unique'            => false,
        )
    );

    $newAttributeId = $installer->getAttributeId('catalog_product', 'wantitnow');

    $installer->run("
    INSERT INTO {$this->getTable('catalog_product_entity_varchar')}
        (entity_id, entity_type_id, attribute_id, value)
        SELECT entity_id, entity_type_id, {$newAttributeId}, '{$defaultValue}' FROM {$this->getTable('catalog_product_entity')}
    ");
    
}
$fp = fopen( dirname(__FILE__) . '/timeslots.txt', 'r');

while ($row = fgets($fp)) {
	$installer->run("INSERT INTO {$this->getTable('webqem_timeslot')} (number_day, description, time_start, time_end) VALUES ".$row);
}

fclose($fp);
$installer->endSetup();
