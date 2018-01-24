<?php return array (
  'table' => 'dvelum_shop_goods_properties',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'parent_object' => '',
  'rev_control' => false,
  'save_history' => false,
  'link_title' => 'field',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'slave_connection' => 'default',
  'log_detalization' => 'default',
  'fields' => 
  array (
    'product_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'dvelum_shop_product',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'field' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'value' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'item_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'dvelum_shop_goods',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
    'product_id' => 
    array (
      'columns' => 
      array (
        0 => 'product_id',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'item_id' => 
    array (
      'columns' => 
      array (
        0 => 'item_id',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'field_value' => 
    array (
      'columns' => 
      array (
        0 => 'field',
        1 => 'value',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
); 