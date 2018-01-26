<?php
declare(strict_types=1);
/**
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
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

use \Exception;

class Goods
{
    protected $code;

    /**
     * Record id
     * @var int $id
     */
    protected $id = 0;

    protected $data;

    /**
     * @var Product
     */
    protected $config;

    /**
     * @param int|string $code
     * @return Goods
     * @throws Exception
     */
    static public function factory($code) : Goods
    {
        return new static($code);
    }

    /**
     * @param string $code
     * @throws Exception
     */
    protected function __construct($code)
    {
        $this->code = $code;
        $this->config = Product::factory($code);
        $this->data['product'] = $code;
    }

    /**
     * Set product id
     * @param int $id
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function setValues(array $data) : void
    {
        foreach ($data as $k=>$v){
            $this->set($k,$v);
        }
    }

    /**
     * Set product property value
     * @param string $key
     * @param mixed $val
     * @throws Exception
     */
    public function set($key, $val) : void
    {
        if($key == 'id'){
            $this->setId($val);
        }

        if(!$this->config->fieldExist($key)){
            throw new Exception('Undefined field '.$key.' for product '.$this->code);
        }

        $field = $this->config->getField($key);

        $value = $field->filter($val);
        if(!$field->isValid($value)){
            throw new Exception('Invalid value for field '.$key);
        }

        $this->data[$key] = $value;
    }

    /**
     * Get product field data
     * @param $key
     * @throws Exception
     * @return mixed
     */
    public function get($key)
    {
        if(!array_key_exists($key,$this->data)){
            if($this->config->fieldExist($key)){
                return null;
            }else{
                throw new Exception('Undefined field '.$key);
            }
        }
        return $this->data[$key];
    }

    /**
     * Check if object has field value
     * @param string $field
     * @return bool
     */
    public function hasValue($field) : bool
    {
        return array_key_exists($field, $this->data);
    }

    /**
     * Get product data
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Get product id
     * @return integer
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get code of product classification
     * @return string
     */
    public function getCode() : string
    {
        return $this->code;
    }

    /**
     * Get Product configuration object
     * @return Product
     */
    public function getConfig() : Product
    {
        return $this->config;
    }
    /**
     * Get Product configuration object
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->getConfig();
    }

    /**
     * Set Item data, skip validation
     * System method
     * @param array $data
     */
    public function setRawData(array $data) : void
    {
        if(isset($data['id'])){
            $this->setId($data['id']);
            unset($data['id']);
        }
        $this->data = $data;
    }
}