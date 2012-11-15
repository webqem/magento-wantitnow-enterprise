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

$installer->endSetup();
