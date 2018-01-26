<?php
return [
    'goods' =>[
        'system_fields'=>'dvelum_shop_fields.php',
    ],
    'product_config' => [
        'object'=>'Dvelum_Shop_Product',
        'lang'=>'dvelum_shop',
    ],
    'storage' =>[
        'adapter' => '\\Dvelum\\Shop\\Storage\\Table',
        'items_object'=> 'dvelum_shop_goods',
        'fields_object' => 'dvelum_shop_goods_properties',
        'item_class' => '\\Dvelum\\Shop\\Goods',
        /*
         *  Event listeners
         * 'eventName' => [[class, (static) method], [class, (static) method]]
         *  event arguments:
         *      \\Dvelum\\Shop\\Event event,
         *      \\Dvelum\\Shop\\Goods $object
         */
        'listeners' => [
            /*
              'beforeSave' => null,
              'afterSave' => null,
              'beforeDelete' => null,
              'afterDelete' => null,
              'beforeInsert' => null,
              'afterInsert' => null,
              'beforeUpdate' => null,
              'afterUpdate' => null,
            */
        ]
    ],
    'images' => [
        'adapter'=>'\\Dvelum\\Shop\\Image\\Medialib',
        // path relative uploads directory (main.php -> uploads)
        'file_path' => 'goods/',
        'category'=>null
    ]
];