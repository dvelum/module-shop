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
namespace Dvelum\App\Backend\Shop\Product;

/**
 *  Articles UI controller
 */
use Dvelum\App\Backend;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;
use Dvelum\App\Controller\EventManager;
use Dvelum\App\Controller\Event;
use Dvelum\Utils;
use Dvelum\Lang;
use Dvelum\Config;

use Dvelum\Shop\Product;

use \Exception;

class Controller extends Backend\Ui\Controller
{
    protected $listFields = ["code","title","id"];
    protected $listLinks = ["category"];
    protected $canViewObjects = ["dvelum_shop_category"];

    public function getModule() : string
    {
        return 'Dvelum_Shop_Product';
    }

    public function  getObjectName()  : string
    {
        return 'Dvelum_Shop_Product';
    }

    public function initListeners()
    {
        $this->eventManager->on(EventManager::AFTER_LIST, [$this, 'prepareList']);
        $this->eventManager->on(EventManager::AFTER_LOAD, [$this, 'prepareData']);
        $this->eventManager->on(EventManager::AFTER_LINKED_LIST, [$this, 'prepareLinkedList']);
    }

    /**
     * Prepare articles list
     * @param Event $event
     * @return void
     */
    public function prepareList(Event $event) : void
    {
        $data = &$event->getData()->data;

        if(empty($data)){
            return;
        }

        try{
            $configList = Product::factoryMultiple(Utils::fetchCol('id', $data));
        }catch (Exception $e){
            echo $e->getMessage();
            Model::factory($this->getObjectName())->logError($e->getMessage());
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        foreach ($data as &$item)
        {
            if(isset($configList[$item['id']])){
                /**
                 * @var Product $cfg
                 */
                $cfg = $configList[$item['id']];
                $item['fields_count'] = count($cfg->getFieldsConfig());
            }
        }unset($item);
    }

    /**
     * Get shop localization dictionary
     * @throws Exception
     * @return Lang\Dictionary
     */
    protected function getLang() : Lang\Dictionary
    {
        $config = Config::storage()->get('dvelum_shop.php')->get('product_config');
        Lang::addDictionaryLoader($config['lang'], $this->appConfig->get('language').'/'. $config['lang'].'.php', Config\Factory::File_Array);
        return Lang::lang($config['lang']);
    }

    /**
     * @param Event $event
     * @return void
     * @throws Exception
     */
    public function prepareData(Event $event) : void
    {
        $data = &$event->getData()->data;

        if(empty($data)) {
            return;
        }

        $productConfig = Product::factory($data['id']);
        $groups = $productConfig->getGroupsConfig();

        $data['fields'] = array_values($productConfig->getFieldsConfig());
        $data['groups'] = array_values($groups);

        foreach ($data['fields'] as &$field)
        {
            if(isset($field['group']) && !empty($field['group']) && isset($groups[$field['group']])){
                $field['group_title'] = $groups[$field['group']]['title'];
            }else{
                $field['group'] = '';
                $field['group_title']  = $this->getLang()->get('noGroup');
            }

            if(array_key_exists('minValue', $field) && !strlen((string)$field['minValue'])){
                $field['minValue'] = null;
            }

            if(array_key_exists('maxValue', $field) && !strlen((string)$field['maxValue'])){
                $field['maxValue'] = null;
            }

            if($field['type']=='boolean'){
                $field['inputValue'] = 1;
                $field['uncheckedValue'] = 0;
                $field['submitValue'] = true;
            }

            if(isset($field['list']) && !empty($field['list'])){
                $listData = [];
                foreach ($field['list'] as $v){
                    $listData[] = ['value'=>$v];
                }
                $field['list'] = $listData;
            }
        }unset($field);

        if(!empty($data['category'])){

            $model = Model::factory('dvelum_shop_category');

            $filters = [
                $model->getPrimaryKey() => Utils::fetchCol('id', $data['category'])
            ];

            $list = $model->query()
                          ->filters($filters)
                          ->fields(['id'=>$model->getPrimaryKey(),'enabled'])
                          ->fetchAll();

            if(!empty($list))
            {
                $list = Utils::rekey('id',$list);
                foreach ($data['category'] as $k=>&$v){
                    if(isset($list[$v['id']])){
                        $v['published'] =  $list[$v['id']]['enabled'];
                    }
                }unset($v);
            }
        }
    }

    /**
     * @param Event $event
     * @return void
     * @throws Exception
     */
    public function prepareLinkedList(Event $event) : void
    {
        $data = &$event->getData()->data;

        if(empty($data)) {
            return;
        }

        $object = $this->request->post('object', 'string', false);

        if(!empty($data) && $object == 'dvelum_shop_category')
        {
            $model = Model::factory('dvelum_shop_category');
            $filters = [
                $model->getPrimaryKey() => Utils::fetchCol('id', $data)
            ];

            $list = $model->query()
                          ->filters($filters)
                          ->fields(['id'=>$model->getPrimaryKey(),'enabled'])
                          ->fetchAll();

            if(!empty($list))
            {
                $list = Utils::rekey('id',$list);
                foreach ($data as $k=>&$v){
                    if(isset($list[$v['id']])){
                        $v['published'] =  $list[$v['id']]['enabled'];
                    }
                }unset($v);
            }
        }
    }

    /**
     * Get product initial properties
     */
    public function getProductDefaultsAction()
    {
        Product::init();
        $this->response->success([
            'fields' => array_values(Product::getSystemFields()),
            'groups' => array_values(Product::getSystemGroups()),
        ]);
    }
    /**
     * Get list of product properties
     */
    public function getProductPropertiesAction()
    {
        $id = $this->request->post('id', 'integer', false);

        if(empty($id)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        try{
            $productConfig = Product::factory($id);
            $groups = $productConfig->getGroupsConfig();
            $list = array_values($productConfig->getFieldsConfig());
            // hide system fields
            foreach ($list as $k=>&$field)
            {
                if(!empty($field['system'])){
                    unset($list[$k]);
                    continue;
                }
                if(isset($field['group']) && !empty($field['group']) && isset($groups[$field['group']])){
                    $field['group_title'] = $groups[$field['group']]['title'];
                }else{
                    $field['group'] = '';
                    $field['group_title']  = $this->getLang()->get('noGroup');
                }

                if(isset($field['list']) && !empty($field['list'])){
                    $listData = [];
                    foreach ($field['list'] as $v){
                        $listData[] = ['value'=>$v];
                    }
                    $field['list'] = $listData;
                }
                
            }unset($field);
            $this->response->success(array_values($list));

        }catch (Exception $e){
            $this->response->error($this->lang->get('CANT_EXEC'));
        }
    }

    /**
     * @throws Exception
     */
    public function getPostedData($objectName) : ?Record
    {
        // convert fields data before save
        $fields = $this->request->post('fields','array',[]);
        if(!empty($fields))
        {
            foreach ($fields as $k=>&$field)
            {
                if(empty($field)){
                    unset($fields[$k]);
                    continue;
                }
                $field = json_decode($field, true);
                if(isset($field['system']) && $field['system']){
                    unset($fields[$k]);
                }
                if($field['type'] == 'list')
                {
                    if(!empty($field['list']))
                    {
                        if(is_array($field['list']) && !empty($field['list'])){
                            $field['list'] = Utils::fetchCol('value', $field['list']);
                        }else{
                            $field['list'] = [];
                        }
                    }else{
                        $field['list'] = [];
                    }
                }else{
                    unset($field['list']);
                }
                unset($field['id']);
                unset($field['group_title']);
            }unset($field);
        }else{
            $fields =[];
        }
        $fields = json_encode($fields);
        $this->request->updatePost('fields', $fields);

        // convert groups data before save
        $groups = $this->request->post('groups','array',[]);
        if(!empty($groups)){
            foreach ($groups as $k=>&$group){
                $group = json_decode($group, true);
                unset($group['id']);
                if(isset($group['system']) && $group['system']){
                    unset($groups[$k]);
                }
            }unset($group);
        }else{
            $groups = [];
        }
        $groups = json_encode($groups);
        $this->request->updatePost('groups', $groups);
        return parent::getPostedData($objectName);
    }
}