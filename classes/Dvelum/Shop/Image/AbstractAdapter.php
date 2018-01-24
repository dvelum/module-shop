<?php
/**
 *  DVelum project http://dvelum.net, http://dvelum.ru, https://github.com/k-samuel/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

abstract class Dvelum_Shop_Image_AbstractAdapter
{
    /**
     * Configuration object
     * @var Config_Abstract
     */
    protected $config;

    public function __construct(Config_Abstract $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config_Abstract
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Add image
     * @param $path
     * @param array $info
     * @return mixed
     */
    abstract function addImage($path, array $info);

    /**
     * Delete image
     * @param $id
     * @return boolean
     */
    abstract public function deleteImage($id);

    /**
     * Get image info
     * @param $id
     * @return array
     */
    abstract public function getImage($id);

    /**
     * Get images info
     * @param array $ids
     * @return array
     */
    abstract public function getImages(array $ids);

    /**
     * Upload images
     * @return array
     */
    abstract public function uploadImages();

}