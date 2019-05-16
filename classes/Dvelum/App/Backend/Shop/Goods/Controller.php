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
namespace Dvelum\App\Backend\Shop\Goods;

use Dvelum\App\Backend;
use Dvelum\Orm\Record;
use Dvelum\Orm\Model;
use Dvelum\Utils;
use Dvelum\Filter;
use \Exception;

use Dvelum\Shop\Storage;
use Dvelum\Shop\Goods;
use Dvelum\Shop\Product;
use Dvelum\Shop\Image;
use Dvelum\Shop\Goods\Form;

class Controller extends Backend\Ui\Controller
{
    protected $listFields = ["title","id","model","product",'images'];
    protected $canViewObjects = ["dvelum_shop_category","dvelum_shop_product"];

    public function getModule() : string
    {
        return 'Dvelum_Shop_Goods';
    }

    public function  getObjectName() : string
    {
        return 'Dvelum_Shop_Goods';
    }

    /**
     * @return array
     * @throws Exception
     * @throws \Dvelum\Orm\Exception
     */
    protected function getList()
    {
        $pager = $this->request->post('pager' , 'array' , []);
        $filter = $this->request->post('filter' , 'array' , []);
        $query = $this->request->post('search' , 'string' , false);

        $storage = Storage::factory();
        $count = $storage->count($filter, $query);
        $data = [];

        if($count)
        {
            $result = $storage->find($filter, $pager, $query);
            /**
             * @var Goods $item
             */
            foreach ($result as $item)
            {
                $fields = $item->getData();
                $fields['id'] = $item->getId();

                foreach ($fields as $field=>$val){
                    if(!in_array($field, $this->listFields,true)){
                        unset($fields[$field]);
                    }
                }
                $data[] = $fields;
            }

            $productIds = Utils::fetchCol('product', $data);
            $products = Record::factory('Dvelum_Shop_Product', $productIds);

            $imageStore = Image::factory();

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
    protected function getData()
    {
        $id = $this->request->post('id' , 'integer' , false);

        if(!$id)
            return [];

        $storage = Storage::factory();
        try{
            $obj = $storage->load($id);
        }catch(Exception $e){
            Model::factory($this->objectName)->logError($e->getMessage());
            return [];
        }

        $data = $obj->getData();
        $data['id'] = $obj->getId();

        $images = $obj->get('images');

        if(!empty($images) && is_array($images))
        {
            $imageStore = Image::factory();
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
        $id = $this->request->post('id' , 'integer' , false);

        if(!$id){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $storage = Storage::factory();
        try{
            $obj = $storage->load($id);
        }catch(Exception $e){
            Model::factory($this->getObjectName())->logError($e->getMessage());
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        $form = new Form();

        $config = $form->backendFormConfig($obj->getConfig());
        $data = $form->backendFormData($obj);

        $this->response->success(['data'=>$data,'config'=>$config]);
    }
    /**
     * Get configuration for new goods by product
     */
    public function loadObjectDefaultsAction()
    {
        $productId = $this->request->post('product', Filter::FILTER_INTEGER, false);
        if(!$productId){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        try{
            $product = Product::factory($productId);
        }catch(Exception $e){
            Model::factory($this->getObjectName())->logError($e->getMessage());
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        $form = new Form();
        $config = $form->backendFormConfig($product);
        $data = ['product'=>$productId];

        $this->response->success(['data'=>$data,'config'=>$config]);
    }

    public function editAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $id = $this->request->post('id' ,'integer', false);
        $product = $this->request->post('product' ,'integer', false);

        if(!$product){
            $this->response->error($this->lang->get('FILL_FORM'),['product'=>$this->lang->get('CANT_BE_EMPTY')]);
            return;
        }

        $storage = Storage::factory();
        try{
            if($id){
                $obj = $storage->load($id);
            }else{
                $obj = Goods::factory($product);
            }
        }catch(Exception $e){
            Model::factory($this->getObjectName())->logError($e->getMessage());
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        if(!$this->applyPostedData($obj)){
            return;
        }

        if(!$storage->save($obj)){
            $this->response->error($this->lang->get('CANT_EXEC'));
        }else{
            $this->response->success(['id'=>$obj->getId()]);
        }
    }

    protected function applyPostedData(Goods $object)
    {
        $productConfig = $object->getConfig();
        $fields = $productConfig->getFields();
        $errors = [];

        $posted = $this->request->postArray();

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
                $errors[$name] = $this->lang->get('CANT_BE_EMPTY');
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

            if($field->isStringType() && !strlen((string)$posted[$name]) ){
                $posted[$name] = null;
            }

            try{
                $object->set($name , $posted[$name]);
            }catch(Exception $e){
                $errors[$name] = $this->lang->get('INVALID_VALUE');
            }
        }

        if(!empty($errors)){
            $this->response->error($this->lang->get('FILL_FORM') , $errors);
            return false;
        }
        return true;
    }

    /**
     * Delete object
     * Sends JSON reply in the result and
     * closes the application
     */
    public function deleteAction()
    {
        if(!$this->checkCanDelete()){
            return;
        }
        $id = $this->request->post('id' , 'integer' , false);

        if(!$id){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $storage = Storage::factory();

        try{
            $object = $storage->load($id);
        }catch(Exception $e){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if(!$storage->delete($object)){
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }
        $this->response->success();
    }

    /**
     * Get common fields for selected goods
     */
    public function commonFieldsAction()
    {
        $id = $this->request->post('id','array',[]);

        if(empty($id) || !is_array($id)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }
        $id = array_map('intval', $id);

        $storage = Storage::factory();

        try{
            $list = $storage->loadItems($id);
        }catch (Exception $e){
            Model::factory($this->objectName)->logError($e->getMessage());
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        if(empty($list)){
            $this->response->success([]);
            return;
        }
        /**
         * @var Product[] $products
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
             * @var \Dvelum\Shop\Product\Field[] $fields
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
            $formBuilder = new Form();
            foreach ($commonFields as $name=>$type){
                $field = $firstProduct->getField($name);
                if($field->getType() == 'text'){
                    continue;
                }
                $cfg = $formBuilder->backendFieldConfig($field);
                $cfg['fieldLabel'] = $this->lang->get('VALUE');
                if($field->isMultiValue()){
                    $cfg['name'] = 'value[]';
                }else{
                    $cfg['name'] = 'value';
                }
                $result[] = ['name'=>$name,'title'=>$field->getTitle(),'cfg'=>$cfg];
            }
        }
        $this->response->success($result);
    }
    /**
     * Set field value for selected goods
     */
    public function setCommonFieldValuesAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $id = $this->request->post('id','array',[]);
        if(empty($id) || !is_array($id)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }
        $id = array_map('intval', $id);
        $field = $this->request->post('field','string','');
        $value = $this->request->post('value','raw',null);

        if(empty($field)){
            $this->response->error($this->lang->get('FILL_FORM'),['field'=>$this->lang->get('CANT_BE_EMPTY')]);
            return;
        }

        $storage = Storage::factory();
        try{
            $list = $storage->loadItems($id);
        }catch (Exception $e){
            Model::factory($this->objectName)->logError($e->getMessage());
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
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
                Model::factory($this->objectName)->logError($e->getMessage());
                $this->response->error($this->lang->get('CANT_EXEC'));
                return;
            }
        }
        $this->response->success(['count'=>$count,'fails'=>implode(', ',$failed)]);
    }

    /**
     * Change product for selected goods
     */
    public function changeProductAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $id = $this->request->post('id','array',[]);
        $product = $this->request->post('product','int',false);
        if(empty($id) || !is_array($id) || empty($product)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }
        $id = array_map('intval', $id);

        $storage = Storage::factory();
        try{
            $list = $storage->loadItems($id);
        }catch (Exception $e){
            Model::factory($this->objectName)->logError($e->getMessage());
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        $count = 0;
        $failed = [];

        try{
            $productObject = Product::factory($product);
        }catch (Exception $e){
            $this->response->error($this->lang->get('FILL_FORM'),['product'=>$this->lang->get('INVALID_VALUE')]);
            return;
        }

        foreach ($list as $item)
        {
            try{
                $newItem = Goods::factory($product);
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
                Model::factory($this->objectName)->logError($e->getMessage());
                $this->response->error($this->lang->get('CANT_EXEC'));
                return;
            }
        }
        $this->response->success(['count'=>$count,'fails'=>implode(', ',$failed)]);
    }
}