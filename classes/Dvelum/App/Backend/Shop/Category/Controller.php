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
namespace Dvelum\App\Backend\Shop\Category;

/**
 *  Articles UI controller
 */
use Dvelum\App\Backend;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;

class Controller extends Backend\Ui\Controller
{
    protected $listFields = ["parent_id","title","code","id","enabled"];
    protected $listLinks = ["parent_id"];
    protected $canViewObjects = ["dvelum_shop_category"];

    public function getModule()  : string
    {
        return 'Dvelum_Shop_Category';
    }

    public function  getObjectName() : string
    {
        return 'Dvelum_Shop_Category';
    }

    /**
     * Get catalog tree
     */
    public function treeListAction()
    {
        /**
         * @var \Model_Dvelum_Shop_Category $categoryModel
         */
        $categoryModel = Model::factory('Dvelum_Shop_Category');
        $this->response->json($categoryModel->getTreeList(['enabled','code','title']));
    }

    /**
     * Update sorting order for category item
     */
    public function sortingAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }
        /**
         * @var \Model_Dvelum_Shop_Category $categoryModel
         */
        $categoryModel = Model::factory('Dvelum_Shop_Category');

        $id = $this->request->post('id','integer',false);
        $newParent = $this->request->post('newparent','integer',false);
        $order = $this->request->post('order', 'array' , []);

        if(!$id || !strlen($newParent) || empty($order)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        try{
            $pObject = Record::factory('Dvelum_Shop_Category' , $id);
            $pObject->set('parent_id', $newParent);
            $pObject->save();
            $categoryModel->updateSortingOrder($order);
            $this->response->success();
        } catch (\Exception $e){
            $this->response->error($this->lang->get('CANT_EXEC'));
        }
    }
}