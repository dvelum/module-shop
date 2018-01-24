<?php return array (
  'table' => 'dvelum_shop_goods',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'parent_object' => '',
  'rev_control' => false,
  'save_history' => false,
  'link_title' => '{model} {title}',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'slave_connection' => 'default',
  'log_detalization' => 'default',
  'fields' => 
  array (
    'product' => 
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
    'title' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'description' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'longtext',
      'db_default' => false,
      'is_search' => false,
      'allow_html' => true,
    ),
    'model' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'external_code' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'images' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'multi',
        'object' => 'medialib',
        'relations_type' => 'many_to_many',
      ),
      'db_type' => 'longtext',
      'db_default' => '',
    ),
    'enabled' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'price' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'decimal',
      'db_default' => false,
      'db_unsigned' => false,
      'db_scale' => 10,
      'db_precision' => 2,
    ),
  ),
  'indexes' => 
  array (
    'external_code' => 
    array (
      'columns' => 
      array (
        0 => 'external_code',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
); 