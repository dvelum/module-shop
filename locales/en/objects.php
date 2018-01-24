<?php return array (
    'dvelum_shop_category' =>
        array (
            'title' => 'Catalog',
            'fields' =>
                array (
                    'parent_id' => 'Parent category',
                    'title' => 'Title',
                    'description' => 'Description',
                    'code' => 'URL code',
                    'enabled' => 'Enabled',
                    'order_no' => 'Sorting order',
                    'external_code' => 'External code',
                ),
        ),
    'dvelum_shop_product' =>
        array (
            'title' => 'Product (classification)',
            'fields' =>
                array (
                    'fields' => 'Fields',
                    'category' => 'Category',
                    'code' => 'URL code',
                    'title' => 'Name',
                    'external_code' => 'External code',
                    'groups' => 'Field groups',
                ),
        ),
    'dvelum_shop_product_category_to_dvelum_shop_category' =>
        array (
            'title' => 'Many to many (relations table) dvelum_shop_product & dvelum_shop_category',
            'fields' =>
                array (
                    'source_id' => 'SOURCE',
                    'target_id' => 'TARGET',
                    'order_no' => 'SORT',
                ),
        ),
    'dvelum_shop_goods' =>
        array (
            'title' => 'Goods (Table adapter)',
            'fields' =>
                array (
                    'product' => 'Product ID (classification)',
                    'field' => 'Field',
                    'value' => 'Value',
                    'item_id' => 'Item ID',
                    'title' => 'Title',
                    'description' => 'Description',
                    'model' => 'Product Model',
                    'external_code' => 'External code',
                    'enabled' => 'Active',
                ),
        ),
    'dvelum_shop_goods_properties' =>
        array (
            'title' => 'Goods Properties (Table adapter)',
            'fields' =>
                array (
                    'product_id' => 'Product ID (classification)',
                    'field' => 'Field',
                    'value' => 'Value',
                    'item_id' => 'Item ID',
                ),
        ),
); 