<?php
return [
    'id' => 'dvelum-module-shop',
    'version' => '2.0.0',
    'author' => 'Kirill Yegorov',
    'name' => 'DVelum Shop',
    'description' => 'Catalog module, requires DVelum >=2.0.0',
    'configs' => './configs',
    'locales' => './locales',
    'resources' =>'./resources',
    'templates' => './templates',
    'vendor'=>'Dvelum',
    'autoloader'=> [
        './classes'
    ],
    'objects' =>[
        'dvelum_shop_category',
        'dvelum_shop_product',
        'dvelum_shop_goods',
        'dvelum_shop_goods_images_to_medialib',
        'dvelum_shop_goods_properties',
        'dvelum_shop_product_category_to_dvelum_shop_category'
    ],
    'post-install'=>'\\Dvelum\\Shop\\Installer'
];