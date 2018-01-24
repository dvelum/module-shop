<?php return array (
  'dvelum_shop_category' => 
  array (
    'title' => 'Каталог',
    'fields' => 
    array (
      'parent_id' => 'Родительский каталог',
      'title' => 'Наименование',
      'description' => 'Описание',
      'code' => 'URL Код',
      'enabled' => 'Включена',
      'order_no' => '№ По порядку',
      'external_code' => 'Код внешней системы',
    ),
  ),
  'dvelum_shop_product' => 
  array (
    'title' => 'Продукт (классификация)',
    'fields' => 
    array (
      'fields' => 'Поля',
      'category' => 'Категории каталога',
      'code' => 'URL код',
      'title' => 'Наименование',
      'external_code' => 'Код внешней системы',
      'groups' => 'Группы полей (свойств)',
    ),
  ),
  'dvelum_shop_product_category_to_dvelum_shop_category' => 
  array (
    'title' => 'Многие ко многим (таблица связей) dvelum_shop_product & dvelum_shop_category',
    'fields' => 
    array (
      'source_id' => 'SOURCE',
      'target_id' => 'TARGET',
      'order_no' => 'SORT',
    ),
  ),
  'dvelum_shop_goods' => 
  array (
    'title' => 'Товары. (адаптер Table)',
    'fields' => 
    array (
      'product' => 'ID Продукта в классификации',
      'field' => 'Поле',
      'value' => 'Значение',
      'item_id' => 'ID Товара',
      'title' => 'Название',
      'description' => 'Описание',
      'model' => 'Артикул',
      'external_code' => 'ID  Внешней системы',
      'images' => 'Изображения',
      'enabled' => 'Активен',
      'price' => 'Цена',
    ),
  ),
  'dvelum_shop_goods_properties' => 
  array (
    'title' => 'Свойства товаров (адаптер Table)',
    'fields' => 
    array (
      'product_id' => 'ID продукта в классификации',
      'field' => 'Поле',
      'value' => 'Значение',
      'item_id' => 'ID товара',
    ),
  ),
  'dvelum_shop_goods_images_to_medialib' => 
  array (
    'title' => 'Многие ко многим (таблица связей) dvelum_shop_goods & medialib',
    'fields' => 
    array (
      'source_id' => 'SOURCE',
      'target_id' => 'TARGET',
      'order_no' => 'SORT',
    ),
  ),
); 