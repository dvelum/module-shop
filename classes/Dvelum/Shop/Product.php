<?php
declare(strict_types=1);
/**
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2018  Kirill Yegorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Dvelum\Shop;

use Dvelum\Lang;
use Dvelum\Orm\Record;
use Dvelum\Orm\Model;
use Dvelum\Config;
use \Exception;

use Dvelum\Shop\Product\Field;

class Product
{
    static protected $instances;
    static protected $initialized = false;
    /**
     * @var array
     */
    static protected $config = null;
    /**
     * @var Lang $lang
     */
    static protected $lang = null;

    protected $id = null;
    protected $model = null;
    protected $data = [];

    /**
     * Create product configuration object
     * @param mixed $id
     * @return Product
     * @throws Exception
     */
    static public function factory($id) : Product
    {
        if(!static::$initialized){
            static::init();
        }

        if(!isset(static::$instances[$id])){
            $config = new static();
            $config->load($id);
            static::$instances[$id] = $config;
        }

        return  static::$instances[$id];
    }

    /**
     * Create multiple product configuration objects
     * @param array $ids
     * @return array Product  [id => Product]
     * @throws Exception
     */
    static public function factoryMultiple(array $ids)
    {
        if(!static::$initialized){
            static::init();
        }

        $result = [];

        foreach ($ids as $index => $id){
            if(isset(static::$instances[$id])){
                $result[$id] =  static::$instances[$id];
                unset($ids[$index]);
            }
        }

        if(!empty($ids)){
            $model = Model::factory(static::$config['object']);
            $data =  Record::factory(static::$config['object'] ,$ids);
            foreach ($ids as $id)
            {
                if(!isset($data[$id])){
                    throw new Exception('Undefined product '.$id);
                }
                $result[$id] = new static();
                $result[$id]->load($id,$data[$id]->getData());
                static::$instances[$id] = $result[$id];
            }
        }
        return $result;
    }

    static public function init()
    {
        $moduleConfig = Config::storage()->get('dvelum_shop.php');
        $productConfig = $moduleConfig->get('product_config');
        $goodsConfig = $moduleConfig->get('goods');

        $fieldsConfigFile = $goodsConfig['system_fields'];
        $systemFields = Config::storage()->get($fieldsConfigFile)->__toArray();

        $systemGroups = Config::storage()->get('dvelum_shop_field_groups.php')->__toArray();
        $appConfig = Config::storage()->get('main.php');

        $productConfig['fields'] = $systemFields;
        $productConfig['groups'] = $systemGroups;

        Lang::addDictionaryLoader($productConfig['lang'], $appConfig->get('language').'/'. $productConfig['lang'].'.php', Config\Factory::File_Array);

        static::$lang = Lang::lang($productConfig['lang']);
        static::$config = $productConfig;
        static::$initialized = true;
    }
    /**
     * Get list of system fields
     * @return array
     */
    static public function getSystemFields()
    {
        return static::$config['fields'];
    }

    /**
     * Get list of system groups
     * @return array
     */
    static public function getSystemGroups()
    {
        return static::$config['groups'];
    }

    /**
     * Dvelum_Shop_Product constructor.
     */
    protected function __construct()
    {
        $this->model = Model::factory(static::$config['object']);
    }

    /**
     * Load product configuration
     * @param $id
     * @param array $data, optional default null
     * @throws \Exception
     */
    protected function load($id, ?array $data = null)
    {
        $this->id = $id;

        if(!empty($data)){
            $this->data = $data;
        }elsE{
            $this->data = Record::factory($this->model->getObjectName(),$id)->getData();

            if(empty($this->data)){
                throw new Exception('Undefined Product '.$id);
            }
        }

        $fields = json_decode($this->data['fields'],true);

        if(empty($fields)){
            $fields = [];
        }
        $this->data['fields'] = $this->initFields($fields);

        if(!empty($this->data['groups'])){
            $groups = json_decode($this->data['groups'],true);
        }

        if(empty($groups)){
            $groups = [];
        }
        $this->data['groups'] = $this->initGroups($groups);
    }
    /**
     * Initialize product fields
     * @param array $data
     * @return array
     */
    protected function initFields(array $data)
    {
        if(!empty($data)){
            $data = array_merge(static::$config['fields'], $data);
        }else{
            $data = static::$config['fields'];
        }

        $result = [];

        foreach ($data as $fieldConfig)
        {
            if(isset($fieldConfig['disabled']) && $fieldConfig['disabled']){
                continue;
            }

            $name = $fieldConfig['name'];

            if(isset($fieldConfig['lazyLang']) && $fieldConfig['lazyLang']){
                $fieldConfig['title'] = static::$lang->get($fieldConfig['title']);
            }
            $fieldClass = '\\Dvelum\\Shop\\Product\\Field';
            $adapterClass = '\\Dvelum\\Shop\\Product\\Field\\'.ucfirst($fieldConfig['type']).'Field';

            if(class_exists($adapterClass)){
                $fieldClass = $adapterClass;
            }

            if(!isset($fieldConfig['multivalue'])){
                $fieldConfig['multivalue'] = false;
            }

            $result[$name] = new $fieldClass($fieldConfig);
        }
        return $result;
    }

    /**
     * Initialize product field groups
     * @param array $groups
     * @return array
     */
    protected function initGroups(array $groups)
    {
        if(!empty($groups)){
            $data = array_merge(static::$config['groups'], $groups);
        }else{
            $data = static::$config['groups'];
        }

        $result = [];

        foreach ($data as &$config)
        {
            if(isset($config['lazyLang']) && $config['lazyLang']){
                $config['title'] = static::$lang->get($config['title']);
            }
            $result[$config['code']] = $config;
        }unset($config);

        return $result;
    }

    /**
     * Get config identifier
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get fields configuration as array
     * @return array
     */
    public function getFieldsConfig()
    {
        foreach ($this->data['fields'] as $name=>$field){
            $result[$name] = $field->__toArray();
        }
        return $result;
    }

    /**
     * Get field grups configuration as array
     * @return array
     */
    public function getGroupsConfig()
    {
        return $this->data['groups'];
    }

    /**
     * Check if product field exists
     * @param $field
     * @return bool
     */
    public function fieldExist($field)
    {
        return isset($this->data['fields'][$field]);
    }

    /**
     * Get field object by name
     * @param string $name
     * @return Field
     */
    public function getField($name)
    {
        return $this->data['fields'][$name];
    }

    /**
     * Get product fields
     * @return Field[]
     */
    public function getFields() : array
    {
        return $this->data['fields'];
    }

    /**
     * Get product title
     * @return mixed
     */
    public function getTitle()
    {
        return $this->data['title'];
    }

    /**
     * Get list of product categories
     */
    public function getCategories()
    {
        return $this->data['category'];
    }
}