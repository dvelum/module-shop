<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
class Dvelum_Backend_Shop_Goods_Controller extends Backend_Controller_Crud
{
    protected $_listFields = ["title","id","model","product",'images'];
    protected $_canViewObjects = ["dvelum_shop_category","dvelum_shop_product"];

    public function getModule()
    {
        return 'Dvelum_Shop_Goods';
    }

    public function  getObjectName()
    {
        return 'Dvelum_Shop_Goods';
    }

    protected function _getList()
    {
        $pager = Request::post('pager' , 'array' , []);
        $filter = Request::post('filter' , 'array' , []);
        $query = Request::post('search' , 'string' , false);

        $storage = Dvelum_Shop_Storage::factory();
        $count = $storage->count($filter, $query);
        $data = [];

        if($count)
        {
            $result = $storage->find($filter, $pager, $query);
            /**
             * @var Dvelum_Shop_Goods $item
             */
            foreach ($result as $item)
            {
                $fields = $item->getData();
                $fields['id'] = $item->getId();

                foreach ($fields as $field=>$val){
                    if(!in_array($field,$this->_listFields,true)){
                        unset($fields[$field]);
                    }
                }
                $data[] = $fields;
            }

            $productIds = Utils::fetchCol('product', $data);
            $products = Db_Object::factory('Dvelum_Shop_Product', $productIds);

            $imageStore = Dvelum_Shop_Image::factory();

            foreach ($data as $k=>&$v)
            {
                if(!empty($v['images'])){
                    $images = $imageStore->getImages($v['images']);
                    if(!empty($images)){
                        $images = array_values($images);
                        $v['img'] = $images[0]['pics']['icon'];
                    }
                }
                unset($v['images']);
                if(isset($products[$v['product']])){
                    $v['product_title'] = $products[$v['product']]->getTitle();
                }
            }unset($v);
        }

        return ['data' =>$data , 'count'=> $count];
    }

    /**
     * Prepare data for loaddataAction
     * @return array
     * @throws Exception
     */
    protected function _getData()
    {
        $id = Request::post('id' , 'integer' , false);

        if(!$id)
            return [];

        $storage = Dvelum_Shop_Storage::factory();
        try{
            $obj = $storage->load($id);
        }catch(Exception $e){
            Model::factory($this->_objectName)->logError($e->getMessage());
            return [];
        }

        $data = $obj->getData();
        $data['id'] = $obj->getId();

        $images = $obj->get('images');

        if(!empty($images) && is_array($images))
        {
            $imageStore = Dvelum_Shop_Image::factory();
            $images = $imageStore->getImages($images);
            foreach ($images as &$image){
                $image = [
                    'id' => $image['id'],
                    'icon' => $image['pics']['thumbnail']
                ];
            }unset($image);
            $data['images'] = array_values($images);
        }else{
            $data['images'] = [];
        }
        return $data;
    }

    public function loadObjectAction()
    {
        $id = Request::post('id' , 'integer' , false);

        if(!$id){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $storage = Dvelum_Shop_Storage::factory();
        try{
            $obj = $storage->load($id);
        }catch(Exception $e){
            Model::factory($this->getObjectName())->logError($e->getMessage());
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }

        $form = new Dvelum_Shop_Goods_Form();

        $config = $form->backendFormConfig($obj->getConfig());
        $data = $form->backendFormData($obj);

        Response::jsonSuccess(['data'=>$data,'config'=>$config]);
    }
    /**
     * Get configuration for new goods by product
     */
    public function loadObjectDefaultsAction()
    {
        $productId = Request::post('product', Filter::FILTER_INTEGER, false);
        if(!$productId){
            Response::jssonError($this->_lang->get('WRONG_REQUEST'));
        }

        try{
            $product = Dvelum_Shop_Product::factory($productId);
        }catch(Exception $e){
            Model::factory($this->getObjectName())->logError($e->getMessage());
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }

        $form = new Dvelum_Shop_Goods_Form();
        $config = $form->backendFormConfig($product);
        $data = ['product'=>$productId];
        Response::jsonSuccess(['data'=>$data,'config'=>$config]);
    }

    public function editAction()
    {
        $this->_checkCanEdit();

        $id = Request::post('id' ,'integer', false);
        $product = Request::post('product' ,'integer', false);

        if(!$product){
            Response::jsonError($this->_lang->get('FILL_FORM'),['product'=>$this->_lang->get('CANT_BE_EMPTY')]);
        }

        $storage = Dvelum_Shop_Storage::factory();
        try{
            if($id){
                $obj = $storage->load($id);
            }else{
                $obj = Dvelum_Shop_Goods::factory($product);
            }
        }catch(Exception $e){
            Model::factory($this->getObjectName())->logError($e->getMessage());
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }

        $this->applyPostedData($obj);

        if(!$storage->save($obj)){
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }else{
            Response::jsonSuccess(['id'=>$obj->getId()]);
        }
    }

    protected function applyPostedData(Dvelum_Shop_Goods $object)
    {
        $productConfig = $object->getConfig();
        $fields = $productConfig->getFields();
        $errors = [];

        $posted = Request::postArray();

        foreach ($fields as $field)
        {
            $name = $field->getName();

            if($name == 'id')
                continue;

            if(
                $field->isRequired()
                    && (
                        !isset($posted[$name])
                        ||
                        (is_string($posted[$name]) && !strlen($posted[$name]))
                        ||
                        (is_array($posted[$name] && empty($posted[$name])))
                     )
            ){
                $errors[$name] = $this->_lang->get('CANT_BE_EMPTY');
                continue;
            }

            if($field->isBoolean() && !isset($posted[$name]))
                $posted[$name] = false;

            if(!array_key_exists($name , $posted)){
                continue;
            }

            if($field->isMultiValue() && empty($posted[$name])){
                $posted[$name] = [];
            }

            try{
                $object->set($name , $posted[$name]);
            }catch(Exception $e){
                $errors[$name] = $this->_lang->get('INVALID_VALUE');
            }
        }

        if(!empty($errors)){
            Response::jsonError($this->_lang->get('FILL_FORM') , $errors);
        }
    }

    /**
     * Delete object
     * Sends JSON reply in the result and
     * closes the application
     */
    public function deleteAction()
    {
        $this->_checkCanDelete();
        $id = Request::post('id' , 'integer' , false);

        if(!$id)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        $storage = Dvelum_Shop_Storage::factory();

        try{
            $object = $storage->load($id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        if(!$storage->delete($object))
            Response::jsonError($this->_lang->get('CANT_EXEC'));

        Response::jsonSuccess();
    }

    /**
     * Get common fields for selected goods
     */
    public function commonFieldsAction()
    {
        $id = Request::post('id','array',[]);
        if(empty($id) || !is_array($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
        $id = array_map('intval', $id);

        $storage = Dvelum_Shop_Storage::factory();

        try{
            $list = $storage->loadItems($id);
        }catch (Exception $e){
            Model::factory($this->_objectName)->logError($e->getMessage());
            $this->_lang->get('CANT_EXEC');
        }

        if(empty($list)){
            Response::jsonSuccess([]);
        }
        /**
         * @var Dvelum_Shop_Product[] $products
         */
        $products = [];
        // collect products
        foreach ($list as $k=>$item)
        {
            $product = $item->getProduct();
            $productId = $product->getId();

            if(!isset($products[$productId])){
                $products[$productId] = $product;
            }
        }

        $commonFields = [];
        $isFirst = true;
        $firstProduct = false;
        foreach ($products as $product)
        {
            /**
             * @var Dvelum_Shop_Product_Field[] $fields
             */
            $fields = $product->getFields();
            if($isFirst){
                $firstProduct = $product;
                foreach ($fields as $name => $item){
                    // skip unique fields
                    if($item->isUnique() || ($name=='images' && $item->isSystem())){
                        continue;
                    }
                    $commonFields[$name] = $item->getType();
                }
                $isFirst = false;
            }else{
               /*
                * Delete field from common if another product has no such field or data types are different
                */
               foreach ($commonFields as $name=>$type){
                   if(!isset($fields[$name]) || $fields[$name]->getType()!==$commonFields[$name]){
                       unset($commonFields[$name]);
                   }
               }
            }
        }

        $result = [];
        if(!empty($commonFields)){
            $formBuilder = new Dvelum_Shop_Goods_Form();
            foreach ($commonFields as $name=>$type){
                $field = $firstProduct->getField($name);
                if($field->getType() == 'text'){
                    continue;
                }
                $cfg = $formBuilder->backendFieldConfig($field);
                $cfg['fieldLabel'] = $this->_lang->get('VALUE');
                if($field->isMultiValue()){
                    $cfg['name'] = 'value[]';
                }else{
                    $cfg['name'] = 'value';
                }
                $result[] = ['name'=>$name,'title'=>$field->getTitle(),'cfg'=>$cfg];
            }
        }

        Response::jsonSuccess($result);
    }
    /**
     * Set field value for selected goods
     */
    public function setCommonFieldValuesAction()
    {
        $this->_checkCanEdit();

        $id = Request::post('id','array',[]);
        if(empty($id) || !is_array($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
        $id = array_map('intval', $id);
        $field = Request::post('field','string','');
        $value = Request::post('value','raw',null);

        if(empty($field)){
            Response::jsonError($this->_lang->get('FILL_FORM'),['field'=>$this->_lang->get('CANT_BE_EMPTY')]);
        }

        $storage = Dvelum_Shop_Storage::factory();
        try{
            $list = $storage->loadItems($id);
        }catch (Exception $e){
            Model::factory($this->_objectName)->logError($e->getMessage());
            $this->_lang->get('CANT_EXEC');
        }

        $count = 0;
        $failed = [];

        foreach ($list as $item){
            try{
                $item->set($field, $value);
                if(!$storage->save($item)){
                    throw new Exception('Cannot save '.$item->getId());
                }
                $count++;
            }catch (Exception $e){
                $failed[] = $item->getId();
                Model::factory($this->_objectName)->logError($e->getMessage());
            }
        }
        Response::jsonSuccess(['count'=>$count,'fails'=>implode(', ',$failed)]);
    }

    /**
     * Change product for selected goods
     */
    public function changeProductAction()
    {
        $this->_checkCanEdit();

        $id = Request::post('id','array',[]);
        $product = Request::post('product','int',false);
        if(empty($id) || !is_array($id) || empty($product)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }
        $id = array_map('intval', $id);

        $storage = Dvelum_Shop_Storage::factory();
        try{
            $list = $storage->loadItems($id);
        }catch (Exception $e){
            Model::factory($this->_objectName)->logError($e->getMessage());
            $this->_lang->get('CANT_EXEC');
        }

        $count = 0;
        $failed = [];

        try{
            $productObject = Dvelum_Shop_Product::factory($product);
        }catch (Exception $e){
            Response::jsonError($this->_lang('FILL_FORM'),['product'=>$this->_lang->get('INVALID_VALUE')]);
        }


        foreach ($list as $item)
        {
            try{
                $newItem = Dvelum_Shop_Goods::factory($product);
                $newItem->setId($item->getId());
                $data = $item->getData();
                // copy values
                foreach ($data as $name=>$value)
                {
                    if($productObject->fieldExist($name)){
                        $newItem->set($name,$value);
                    }
                }
                if(!$storage->save($newItem)){
                    throw new Exception('Cannot save '.$newItem->getId());
                }
                $count++;
            }catch (Exception $e){
                $failed[] = $item->getId();
                Model::factory($this->_objectName)->logError($e->getMessage());
            }
        }
        Response::jsonSuccess(['count'=>$count,'fails'=>implode(', ',$failed)]);
    }
}