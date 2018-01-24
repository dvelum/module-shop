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
namespace Dvelum\Shop\Product\Field;
use Dvelum\Shop\Product\Field;

class NumberField extends Field
{
    /**
     * Validate Value
     * @param $value
     * @return boolean
     */
    public function isValid($value)
    {
        if($this->config['multivalue']){
            if(!is_array($value)){
                return false;
            }else{
                foreach ($value as $item){
                    if(!$this->checkValue($item)){
                        return false;
                    }
                }
            }
            return true;
        }else{
            return $this->checkValue($value);
        }
    }

    protected function checkValue($value)
    {
        if(!is_numeric($value)){
            return false;
        }

        if(isset($this->config['minValue']) && strlen((string)$this->config['minValue'])){
            if($value < $this->config['minValue']){
                return false;
            }
        }

        if(isset($this->config['maxValue']) && strlen((string)$this->config['maxValue'])){
            if($value > $this->config['maxValue']){
                return false;
            }
        }
        return true;
    }

    /**
     * Filter value
     * @param mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if($this->config['multivalue']){
            if(!is_array($value)){
                return [filter_var($value,FILTER_SANITIZE_NUMBER_INT)];
            }else{
                foreach ($value as &$item){
                    $item = filter_var($item,FILTER_SANITIZE_NUMBER_INT);
                }unset($item);
                return array_values($value);
            }
        }else{
            return filter_var($value,FILTER_SANITIZE_NUMBER_INT);
        }
    }
}