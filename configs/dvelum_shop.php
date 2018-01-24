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
        'adapter' => 'Dvelum_Shop_Storage_Table',
        'items_object'=> 'dvelum_shop_goods',
        'fields_object' => 'dvelum_shop_goods_properties',
        'item_class' => 'Dvelum_Shop_Goods',
        /*
         *  Event listeners
         * 'eventName' => [[class, (static) method], [class, (static) method]]
         *  event arguments:
         *      Dvelum_Shop_Event event,
         *      Dvelum_Shop_Goods $object
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
        'adapter'=>'Dvelum_Shop_Image_Medialib',
        // path relative uploads directory (main.php -> uploads)
        'file_path' => 'goods/',
        'category'=>null
    ]
];