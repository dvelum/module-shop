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

class Model_Dvelum_Shop_Category extends Model
{
    /**
     * Get Ext Tree structure
     * @param array $fields
     * @return array
     */
    public function getTreeList(array $fields)
    {
        //No recursion
        $node = Request::post('node', 'string', null);
        if(!empty($node)){
            Response::jsonSuccess();
        }
        /*
         * Add the required fields to the list
         */
        $fields = array_unique(array_merge(['id','parent_id','order_no'],$fields));
        $data = $this->getList(['sort'=>'order_no','dir'=>'ASC'], false, $fields);

        if(empty($data))
            return [];

        $tree = new Tree();

        foreach($data as $value)
        {
            if(empty($value['parent_id'])){
                $value['parent_id'] = 0;
            }

            $tree->addItem($value['id'], $value['parent_id'], $value , $value['order_no']);
        }

        return $this->createTreeStructure($tree , 0);
    }

    /**
     * Fill child data array for tree panel
     * @param Tree $tree
     * @param mixed $root
     * @return array
     */
    protected function createTreeStructure(Tree $tree , $root = 0 )
    {
        $result = [];
        $items = $tree->getChilds($root);

        if(empty($items))
            return [];

        foreach($items as $k=>$v)
        {
            $row = $v['data'];

            $obj = new stdClass();
            $obj->id = $row['id'];
            $obj->text = $row['title'] . ' <i>('.$row['code'].')</i>';
            $obj->expanded = true;
            $obj->leaf = false;
            $obj->allowDrag = true;

            if(!$row['enabled']){
                $obj->iconCls = 'pageHidden';
            }

            $data = [];

            if($tree->hasChilds($row['id'])){
                $data = $this->createTreeStructure($tree ,  $row['id']);
            }
            $obj->children = $data;

            $result[] = $obj;
        }
        return $result;
    }

    /**
     * Update sorting order for category items by id
     * @param array $sortingOrder - object id's
     * @throws Exception
     */
    public function updateSortingOrder(array $sortingOrder)
    {
        $i=0;
        foreach ($sortingOrder as $v)
        {
            $obj = Db_Object::factory($this->getObjectName(), intval($v));
            $obj->set('order_no', $i);
            $obj->save();
            $i++;
        }
    }
}