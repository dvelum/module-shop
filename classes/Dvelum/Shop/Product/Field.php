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
namespace Dvelum\Shop\Product;

class Field
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get field name
     * @return string
     */
    public function getName()
    {
        return $this->config['name'];
    }

    /**
     * Get field title (label)
     * @return string
     */
    public function getTitle()
    {
        return $this->config['title'];
    }

    /**
     * Get data as array
     * @return array
     */
    public function __toArray()
    {
        return $this->config;
    }

    /**
     * Validate Value
     * @param $value
     * @return boolean
     */
    public function isValid($value)
    {
        if(is_string($value) || is_bool($value) || is_numeric($value) || is_null($value)){
            return true;
        }
        return false;
    }

    /**
     * Filter value
     * @param mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if($this->config['multivalue'] && !is_array($value)){
            $value = [$value];
        }
        return $value;
    }

    public function isList()
    {
        if($this->config['type'] == 'list'){
            return true;
        }
        return false;
    }

    /**
     * Is system field
     * @return bool
     */
    public function isSystem() : bool
    {
        if(isset($this->config['system']) && $this->config['system']){
            return true;
        }
        return false;
    }

    /**
     * Is multi-value field
     * @return bool
     */
    public function isMultiValue() : bool
    {
        if(isset($this->config['multivalue']) && $this->config['multivalue']){
            return true;
        }
        return false;
    }

    /**
     * Is required field
     * @return bool
     */
    public function isRequired()   : bool
    {
        if(isset($this->config['required']) && $this->config['required']){
            return true;
        }
        return false;
    }

    /**
     * Get field type
     * @return mixed
     */
    public function getType()
    {
        return $this->config['type'];
    }

    /**
     * Get field config
     * @return array
     */
    public function getConfig() : array
    {
        return $this->config;
    }

    /**
     * Get min value
     * @return int|null
     */
    public function getMinValue(): ?int
    {
        if(isset($this->config['minValue']) && strlen((string)$this->config['minValue'])){
            return  (int) $this->config['minValue'];
        }
        return null;
    }

    /**
     * Get min value
     * @return int|null
     */
    public function getMaxValue(): ?int
    {
        if(isset($this->config['maxValue']) && strlen((string)$this->config['maxValue'])){
            return  (int) $this->config['maxValue'];
        }
        return null;
    }

    /**
     * Get accepted values
     * @return array
     */
    public function getList() : array
    {
        if(isset($this->config['list']) && !empty($this->config['list'])){
            return  $this->config['list'];
        }
        return [];
    }

    /**
     * Check field type
     * @return bool
     */
    public function isBoolean() : bool
    {
        if($this->config['type'] ==='boolean'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get field group
     * @return string
     */
    public function getGroup() : string
    {
        if(isset($this->config['group'])){
            return $this->config['group'];
        }else{
            return '';
        }
    }

    /**
     * Check field unique
     * @return bool
     */
    public function isUnique() : bool
    {
        if(isset($this->config['unique']) && $this->config['unique']){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Check if field contains characters
     * @return bool
     */
    public function isStringType() : bool
    {
        return $this instanceof \Dvelum\Shop\Product\Field\StringField;
    }
}