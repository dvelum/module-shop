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
class Dvelum_Shop_Storage_Table extends Dvelum_Shop_Storage_AbstractAdapter
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

    public function __construct(Config_Abstract $config)
    {
        parent::__construct($config);
        $this->itemsModel = Model::factory($config->get('items_object'));
        $this->fieldsModel = Model::factory($config->get('fields_object'));
    }

    /**
     * Load goods by id
     * @param integer $id
     * @return Dvelum_Shop_Goods
     * @throws Exception
     */
    public function load($id)
    {
        $object = Db_Object::factory($this->config->get('items_object'), $id);
        $item = $object->getData();

        $data = $this->fieldsModel->getList(false, ['item_id'=>$id]);

        $productCode = $item['product'];

        $dataObject = $this->config->get('item_class');

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
     * @return Dvelum_Shop_Goods[]
     * @throws Exception
     */
    public function loadItems(array $id)
    {
        $goods = Db_Object::factory($this->config->get('items_object'), $id);
        $fields = $this->fieldsModel->getList(false,['item_id'=>$id]);
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
            $object = $dataObject::factory($itemData['product']);
            $object->setRawData($itemData);
            $result[$itemId] = $object;
        }
        return $result;
    }

    /**
     * Save item
     * @param Dvelum_Shop_Goods $item
     * @return bool
     */
    public function save(Dvelum_Shop_Goods $item)
    {
        $isNew = (boolean) $item->getId();
        $this->fireEvent(Dvelum_Shop_Event::BEFORE_SAVE, $item);
        if($isNew){
            $this->fireEvent(Dvelum_Shop_Event::BEFORE_INSERT, $item);
        }else{
            $this->fireEvent(Dvelum_Shop_Event::BEFORE_UPDATE, $item);
        }

        $fields = $item->getConfig()->getFields();
        $data = $item->getData();

        $system = [];
        $properties = [];

        $goodsId = $item->getId();
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
            $o = Db_Object::factory($this->config->get('items_object'), $item->getId());
            $o->setValues($system);

            if(!$o->save(false)){
                $itemsDb->rollBack();
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
                if(!$this->fieldsModel->multiInsert($properties)){
                    return false;
                }
            }
            $itemsDb->commit();
            $this->fireEvent(Dvelum_Shop_Event::AFTER_SAVE, $item);
            if($isNew){
                $this->fireEvent(Dvelum_Shop_Event::AFTER_INSERT, $item);
            }else{
                $this->fireEvent(Dvelum_Shop_Event::AFTER_UPDATE, $item);
            }
        }catch (Exception $e){
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

        $config = Db_Object_Config::getInstance($this->itemsModel->getObjectName());

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


        // add filters for system fields
        if(!empty($sysFilter)){
            $this->itemsModel->queryAddFilters($sql, $sysFilter);
        }

        // add fields filters
        if(!empty($fieldFilter)){
            $this->applyFieldFilters($sql);
        }

        // add text search
        if(!empty($query)){
            $this->itemsModel->queryAddSearchString($sql, $query, false, '%[s]%');
        }
        // add params
        if(!empty($params)){
            Model::queryAddPagerParams($sql,$params);
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
     * @return integer
     */
    public function count($filters = false, $query = false)
    {
        $sysFilter = [];
        $fieldFilter = [];

        $config = Db_Object_Config::getInstance($this->itemsModel->getObjectName());

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


        // add filters for system fields
        if(!empty($sysFilter)){
            $this->itemsModel->queryAddFilters($sql, $sysFilter);
        }

        // add fields filters
        if(!empty($fieldFilter)){
            $this->applyFieldFilters($sql);
        }

        // add text search
        if(!empty($query)){
            $this->itemsModel->queryAddSearchString($sql, $query, false, '%[s]%');
        }

        try{
            return $db->fetchOne($sql);
        }catch (Exception $e){
            echo $e->getMessage();
            $this->itemsModel->logError($e->getMessage());
            return 0;
        }
    }

    protected function applyFieldFilters($sql, array $fieldFilters)
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

            if($filter instanceof Db_Select_Filter)
            {
                $sqlPrefix = '`field` ='.$db->quote($filter->field).' AND ';

                switch ($filter->type){
                    case Db_Select_Filter::LT:
                    case Db_Select_Filter::GT:
                    case Db_Select_Filter::GT_EQ:
                    case Db_Select_Filter::LT_EQ:
                    case Db_Select_Filter::NOT_NULL :
                    case Db_Select_Filter::IS_NULL :
                    case Db_Select_Filter::BETWEEN:
                    case Db_Select_Filter::NOT_BETWEEN:
                        throw new Exception('Dvelum_Shop_Storage_Table does not support query filter "'.$filter->type.'"');
                        break;
                    case Db_Select_Filter::LIKE:
                    case Db_Select_Filter::NOT_LIKE:
                        if(is_array($filter->value)) {
                            throw new Exception('Dvelum_Shop_Storage_Table does not support query multiple filter "' . $filter->type . '"');
                        }
                        $subSelect->$method($sqlPrefix.' `value` ' . $filter->type . ' '.$db->quote('%' .  $filter->value . '%'));
                        break;
                    case Db_Select_Filter::EQ:
                    case Db_Select_Filter::NOT:
                        $subSelect->$method($sqlPrefix.' `value` ' . $filter->type . ' ?' , $filter->value);
                        break;
                    case Db_Select_Filter::IN:
                    case Db_Select_Filter::NOT_IN:
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
     * @param Dvelum_Shop_Goods $item
     * @return boolean
     */
    public function delete(Dvelum_Shop_Goods $item)
    {
        $this->fireEvent(Dvelum_Shop_Event::BEFORE_DELETE, $item);
        try{
            $db = $this->itemsModel->getDbConnection();
            $db->beginTransaction();

            $o = Db_Object::factory($this->config->get('items_object'), $item->getId());

            if(!$o->delete(false)){
                throw new Exception('Cannot delete object ', $o->getId());
            }

            $fieldsDb = $this->fieldsModel->getDbConnection();
            $fieldsDb->delete($this->fieldsModel->table(),'item_id ='.intval($item->getId()));
            $db->commit();
            $this->fireEvent(Dvelum_Shop_Event::AFTER_DELETE, $item);
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
     * @return boolean
     */
    public function itemExist($id)
    {
        return (boolean)$this->count(['id'=>$id]);
    }
}