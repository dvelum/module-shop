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

use Dvelum\Orm\Model;
use Dvelum\Orm\Record;
use Dvelum\Config\ConfigInterface;
use Dvelum\Utils;

use Dvelum\Db\Select;
use Dvelum\Db\Select\Filter;

use \Exception;

use Dvelum\Shop\Goods;
use Dvelum\Shop\Event;

/**
 * Simple product storage.
 * All data saves to 1 DB table
 * product_id - int
 * field - varchar
 * value - text
 *
 * Advantages:
 *  - simple administration
 *  - simple sphinx integration
 *
 * Disadvantages:
 *  - only exact match filtering
 *  - data types ignored
 *  - not optimal storage size
 */
class Table extends AbstractAdapter
{
    /**
     * @var Model $itemsModel
     */
    protected $itemsModel;
    /**
     * @var Model $fieldsModel
     */
    protected $fieldsModel;
    /**
     * @var Model $imagesModel
     */
    protected $imagesModel;

    public function __construct(ConfigInterface $config)
    {
        parent::__construct($config);
        $this->itemsModel = Model::factory($config->get('items_object'));
        $this->fieldsModel = Model::factory($config->get('fields_object'));
    }

    /**
     * Load goods by id
     * @param integer $id
     * @return Goods
     * @throws Exception
     */
    public function load($id) : Goods
    {
        $object = Record::factory($this->config->get('items_object'), $id);
        $item = $object->getData();

        $data = $this->fieldsModel->query()
            ->filters(['item_id'=>$id])
            ->fetchAll();

        $productCode = $item['product'];

        $dataObject = $this->config->get('item_class');

        /**
         * @var Goods $goodsObject
         */
        $goodsObject = $dataObject::factory($productCode);

        $product = $goodsObject->getConfig();

        $itemData = $object->getData();

        foreach ($data as $item)
        {
            $field = $item['field'];
            if($product->fieldExist($field))
            {
                $fieldConfig = $product->getField($field);
                if($fieldConfig->isMultiValue())
                {
                    if(!isset($itemData[$field])){
                        $itemData[$field] = [];
                    }
                    $itemData[$field][] = $item['value'];
                }else{
                    $itemData[$field] = $item['value'];
                }
            }else{
                if(isset($itemData[$field]) && !is_array($itemData[$field])){
                    $itemData[$field] = [$itemData[$field],$item['value']];
                }else{
                    $itemData[$field] = $item['value'];
                }
            }
        }
        $goodsObject->setRawData($itemData);
        return $goodsObject;
    }
    /**
     * Load multiple items
     * @param array $id
     * @return Goods[]
     * @throws Exception
     */
    public function loadItems(array $id) : array
    {
        /**
         * @var Record[]
         */
        $goods = Record::factory($this->config->get('items_object'), $id);

        $fields = $this->fieldsModel->query()
            ->filters(['item_id'=>$id])
            ->fetchAll();

        if(!empty($fields)){
            $fields = Utils::groupByKey('item_id', $fields);
        }
        $result = [];

        $dataObject = $this->config->get('item_class');

        foreach ($id as $itemId)
        {
            if(!isset($goods[$itemId])){
                throw new Exception('Undefined Goods ID:'.$itemId);
            }
            $itemData = $goods[$itemId]->getData();

            if(isset($fields[$itemId])){
                foreach ($fields[$itemId] as $property){
                    if(!isset($itemData[$property['field']])){
                        $itemData[$property['field']] = $property['value'];
                    }
                }
            }
            /**
             * @var Goods $object
             */
            $object = $dataObject::factory($itemData['product']);
            $object->setRawData($itemData);
            $result[$itemId] = $object;
        }
        return $result;
    }

    /**
     * Save item
     * @param Goods $item
     * @return bool
     */
    public function save(Goods $item) : bool
    {
        $isNew = (boolean) $item->getId();
        $this->fireEvent(Event::BEFORE_SAVE, $item);
        if($isNew){
            $this->fireEvent(Event::BEFORE_INSERT, $item);
        }else{
            $this->fireEvent(Event::BEFORE_UPDATE, $item);
        }

        $fields = $item->getConfig()->getFields();
        $data = $item->getData();

        $system = [];
        $properties = [];

        $productCode = $item->getCode();

        $system['product'] = $productCode;

        foreach ($fields as $name=>$field)
        {
            if(!isset($data[$name]))
                continue;

            if($field->isSystem()){
                $system[$name] = $data[$name];
            }else{
                if($field->isMultiValue())
                {
                    if(!empty($data[$name]))
                    {
                        foreach ($data[$name] as $val){
                            $properties[] = [
                                'product_id'=> $productCode,
                                'value' => $val,
                                'field' => $name
                            ];
                        }
                    }
                }else{
                    $properties[] = [
                        'product_id'=> $productCode,
                        'value' => $data[$name],
                        'field' => $name
                    ];
                }
            }
        }

        $itemsDb = $this->itemsModel->getDbConnection();

        try{
            $itemsDb->beginTransaction();
            $o = Record::factory($this->config->get('items_object'), $item->getId());
            $o->setValues($system);

            if(!$o->save(false)){
                throw new Exception('Cannot save '.$this->config->get('items_object'));
            }
            $id = $o->getId();
            $item->setId($id);

            foreach ($properties as $k=>&$v){
                $v['item_id'] = $id;
            }unset($v);

            $fieldsDb = $this->fieldsModel->getDbConnection();
            $fieldsDb->delete($this->fieldsModel->table(),'item_id ='.intval($item->getId()));

            if(!empty($properties)){
                $insert = new Model\Insert($this->fieldsModel);
                if(!$insert->bulkInsert($properties)){
                    return false;
                }
            }
            $itemsDb->commit();
            $this->fireEvent(Event::AFTER_SAVE, $item);
            if($isNew){
                $this->fireEvent(Event::AFTER_INSERT, $item);
            }else{
                $this->fireEvent(Event::AFTER_UPDATE, $item);
            }
        }catch (Exception $e){
            echo $e->getMessage();
            $itemsDb->rollBack();
            $this->itemsModel->logError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Find items
     * @param array|boolean $filters
     * @param array|boolean $params (sorting, limit)
     * @param string|boolean $query (text search)
     * @return array|boolean - boolean false on error
     * @throws Exception
     */
    public function find($filters = false, $params = false, $query = false)
    {
        $sysFilter = [];
        $fieldFilter = [];

        $config = Record\Config::factory($this->itemsModel->getObjectName());

        if(!empty($filters))
        {
            foreach ($filters as $field=>$filter){
                if($config->fieldExists($field)){
                    $sysFilter[$field] = $filter;
                }else{
                    $fieldFilter[$field] = $filter;
                }
            }
        }

        $db = $this->itemsModel->getDbConnection();
        $sql = $db->select()->from($this->itemsModel->table(),['id']);

        $queryObject = new Model\Query($this->itemsModel);
        $queryObject->applyFilters($sql, $filters);

        // add filters for system fields
        if(!empty($sysFilter)){
            $queryObject->applyFilters($sql, $sysFilter);
        }

        // add fields filters
        if(!empty($fieldFilter)){
            $this->applyFieldFilters($sql, $fieldFilter);
        }

        // add text search
        if(!empty($query)){
            $queryObject->applySearch($sql, $query, Model\Query::SEARCH_TYPE_CONTAINS);
        }

        // add params
        if(!empty($params)){
            $queryObject->applyParams($sql, $params);
        }

        try{
            $list = $db->fetchCol($sql);
            if(empty($list)){
                return [];
            }
            return $this->loadItems($list);
        }catch (Exception $e){
            $this->itemsModel->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Get items count
     * @param array|boolean $filters
     * @param string|boolean $query (text search)
     * @return int
     * @throws Exception
     */
    public function count($filters = false, $query = false) : int
    {
        $sysFilter = [];
        $fieldFilter = [];

        $config = Record\Config::factory($this->itemsModel->getObjectName());

        if(!empty($filters))
        {
            foreach ($filters as $field=>$filter){
                if($config->fieldExists($field)){
                    $sysFilter[$field] = $filter;
                }else{
                    $fieldFilter[$field] = $filter;
                }
            }
        }


        $db = $this->itemsModel->getDbConnection();
        $sql = $db->select()->from($this->itemsModel->table(),['count'=>'COUNT(*)']);

        $queryObject = new Model\Query($this->itemsModel);

        // add filters for system fields
        if(!empty($sysFilter)){
            $queryObject->applyFilters($sql, $sysFilter);
        }

        // add fields filters
        if(!empty($fieldFilter)){
            $queryObject->applyFilters($sql, $fieldFilter);
        }

        // add text search
        if(!empty($query)){
            $queryObject->applySearch($sql, $query, Model\Query::SEARCH_TYPE_CONTAINS);
        }

        try{
            return (int) $db->fetchOne($sql);
        }catch (Exception $e){
            $this->itemsModel->logError($e->getMessage());
            return 0;
        }
    }

    /**
     * @param Select $sql
     * @param array $fieldFilters
     * @throws Exception
     */
    protected function applyFieldFilters(Select $sql, array $fieldFilters)
    {
        $db = $this->fieldsModel->getDbConnection();
        $subSelect = $db->select()->distinct()->from($this->fieldsModel->table(),['item_id']);

        $first = true;
        foreach ($fieldFilters as $field=>$filter)
        {
            if($first){
                $method = 'where';
            }else{
                $method = 'orWhere';
            }

            if($filter instanceof Filter)
            {
                $sqlPrefix = '`field` ='.$db->quote($filter->field).' AND ';

                switch ($filter->type){
                    case Filter::LT:
                    case Filter::GT:
                    case Filter::GT_EQ:
                    case Filter::LT_EQ:
                    case Filter::NOT_NULL :
                    case Filter::IS_NULL :
                    case Filter::BETWEEN:
                    case Filter::NOT_BETWEEN:
                        throw new Exception('Dvelum_Shop_Storage_Table does not support query filter "'.$filter->type.'"');
                        break;
                    case Filter::LIKE:
                    case Filter::NOT_LIKE:
                        if(is_array($filter->value)) {
                            throw new Exception('Dvelum_Shop_Storage_Table does not support query multiple filter "' . $filter->type . '"');
                        }
                        $subSelect->$method($sqlPrefix.' `value` ' . $filter->type . ' '.$db->quote('%' .  $filter->value . '%'));
                        break;
                    case Filter::EQ:
                    case Filter::NOT:
                        $subSelect->$method($sqlPrefix.' `value` ' . $filter->type . ' ?' , $filter->value);
                        break;
                    case Filter::IN:
                    case Filter::NOT_IN:
                        $subSelect->$method($sqlPrefix.' `value` ' . $filter->type . ' (?)' , $filter->value);
                        break;
                }
            }else{
                if(is_array($filter)){
                    $subSelect->$method('`field` ='.$db->quote($field).' AND `value` IN(?)', $filter);
                }else{
                    $subSelect->$method('`field` ='.$db->quote($field).' AND `value` =?', $filter);
                }

            }
            $first = false;
        }
        $sql->where('`id` IN('.substr($subSelect->__toString(),0,-1).')');
    }

    /**
     * Delete item
     * @param Goods $item
     * @return boolean
     */
    public function delete(Goods $item) : bool
    {
        $this->fireEvent(Event::BEFORE_DELETE, $item);
        try{
            $db = $this->itemsModel->getDbConnection();
            $db->beginTransaction();

            $o = Record::factory($this->config->get('items_object'), $item->getId());

            if(!$o->delete(false)){
                throw new Exception('Cannot delete object ', $o->getId());
            }

            $fieldsDb = $this->fieldsModel->getDbConnection();
            $fieldsDb->delete($this->fieldsModel->table(),'item_id ='.intval($item->getId()));
            $db->commit();
            $this->fireEvent(Event::AFTER_DELETE, $item);
        }catch (Exception $e){
            $this->itemsModel->logError($e->getMessage());
            $db->rollBack();
            return false;
        }
        return true;
    }

    /**
     * Check item ID
     * @param $id
     * @return bool
     */
    public function itemExist($id) : bool
    {
        return (boolean) $this->count(['id'=>$id]);
    }
}