<?php return array (
  'table' => 'dvelum_shop_category',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'parent_object' => '',
  'rev_control' => false,
  'save_history' => true,
  'link_title' => 'title',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'slave_connection' => 'default',
  'log_detalization' => 'extended',
  'fields' => 
  array (
    'parent_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'dvelum_shop_category',
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
    'code' => 
    array (
      'type' => '',
      'unique' => 'code',
      'db_isNull' => false,
      'required' => true,
      'validator' => 'Validator_Pagecode',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
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
    'order_no' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'int',
      'db_default' => 0,
      'db_unsigned' => true,
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
      'is_search' => false,
      'allow_html' => false,
    ),
  ),
  'indexes' => 
  array (
    'code' => 
    array (
      'columns' => 
      array (
        0 => 'code',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'parent_id' => 
    array (
      'columns' => 
      array (
        0 => 'parent_id',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
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