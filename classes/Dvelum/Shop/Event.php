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

class Event
{
    const BEFORE_SAVE = 'beforeSave';
    const AFTER_SAVE = 'afterSave';
    const BEFORE_DELETE = 'beforeDelete';
    const AFTER_DELETE = 'afterDelete';
    const BEFORE_INSERT = 'beforeInsert';
    const AFTER_INSERT = 'afterInsert';
    const BEFORE_UPDATE = 'beforeUpdate';
    const AFTER_UPDATE = 'afterUpdate';

    protected $type = '';
    protected $eventData = [];
    /**
     * Dvelum_Shop_Event constructor.
     * @param string $type
     * @param array $data
     */
    public function __construct($type, array $data = [])
    {
        $this->type = $type;
        $this->eventData = $data;
    }

    /**
     * Get event type
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Get event data
     * @return array
     */
    public function getData() : array
    {
        return $this->eventData;
    }
}