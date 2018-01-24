<?php return array (
  'table' => 'dvelum_shop_product',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'parent_object' => '',
  'rev_control' => false,
  'save_history' => false,
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
    'fields' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'longtext',
      'db_default' => false,
      'is_search' => false,
      'allow_html' => true,
    ),
    'category' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'multi',
        'object' => 'dvelum_shop_category',
        'relations_type' => 'many_to_many',
      ),
      'db_type' => 'longtext',
      'db_default' => '',
    ),
    'code' => 
    array (
      'type' => '',
      'unique' => 'code',
      'db_isNull' => true,
      'required' => false,
      'validator' => 'Validator_Pagecode',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
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
    'groups' => 
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