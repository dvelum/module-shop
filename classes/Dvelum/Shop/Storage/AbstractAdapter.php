<?php
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
declare(strict_types=1);

namespace Dvelum\Shop\Storage;

use Dvelum\Config\ConfigInterface;
use Dvelum\Shop\Goods;
use Dvelum\Shop\Event;

use \Exception;

abstract class AbstractAdapter
{
    /**
     * Storage configuration
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Event listeners
     * @var array
     */
    protected $listeners = [];

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $listeners  = $config->get('listeners');
        if(!empty($listeners))
        {
            foreach ($listeners as $event => $items)
            {
                $this->listeners[$event] = $items;
            }
        }
    }

    /**
     * Fire storage event
     * @param string $eventType
     * @param Goods $object
     * @return void
     */
    public function fireEvent($eventType, Goods $object) : void
    {
        if(isset($this->listeners[$eventType]) && !empty($this->listeners[$eventType])){
            $event = new Event($eventType);
            foreach ($this->listeners[$eventType] as $item){
                call_user_func_array($item,[$event,$object]);
            }
        }
    }

    /**
     * Load goods by id
     * @param int $id
     * @return Goods
     * @throws Exception
     */
    abstract public function load(int $id) : Goods;

    /**
     * Check item ID
     * @param $id
     * @return bool
     */
    abstract public function itemExist($id) : bool;

    /**
     * Load multiple items
     * @param array $id
     * @return Goods[]
     * @throws Exception
     */
    abstract public function loadItems(array $id) : array;

    /**
     * Save item
     * @param Goods $item
     * @return bool
     */
    abstract public function save(Goods $item) : bool;

    /**
     * Find items
     * @param array|boolean $filters
     * @param array|boolean $params (sorting, limit)
     * @param string|boolean $query (text search)
     * @return mixed
     */
    abstract public function find($filters = false, $params = false, $query = false);

    /**
     * Get items count
     * @param array|boolean $filters
     * @param string|boolean $query (text search)
     * @return int
     */
    abstract public function count($filters = false, $query = false) : int;

    /**
     * Delete item
     * @param Goods $item
     * @return bool
     */
    abstract public function delete(Goods $item) : bool;
}