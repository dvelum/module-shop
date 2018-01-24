<?php
abstract class Dvelum_Shop_Storage_AbstractAdapter
{
    /**
     * Storage configuration
     * @var Config_Abstract
     */
    protected $config;

    /**
     * Event listeners
     * @var array
     */
    protected $listeners = [];

    public function __construct(Config_Abstract $config)
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
     * @param Dvelum_Shop_Goods $object
     */
    public function fireEvent($eventType, Dvelum_Shop_Goods $object)
    {
        if(isset($this->listeners[$eventType]) && !empty($this->listeners[$eventType])){
            $event = new Dvelum_Shop_Event($eventType);
            foreach ($this->listeners[$eventType] as $item){
                call_user_func_array($item,[$event,$object]);
            }
        }
    }

    /**
     * Load goods by id
     * @param integer $id
     * @return Dvelum_Shop_Goods
     * @throws Exception
     */
    abstract public function load($id);

    /**
     * Check item ID
     * @param $id
     * @return mixed
     */
    abstract public function itemExist($id);

    /**
     * Load multiple items
     * @param array $id
     * @return Dvelum_Shop_Goods[]
     * @throws Exception
     */
    abstract public function loadItems(array $id);

    /**
     * Save item
     * @param Dvelum_Shop_Goods $item
     * @return boolean
     */
    abstract public function save(Dvelum_Shop_Goods $item);

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
     * @return integer
     */
    abstract public function count($filters = false, $query = false);

    /**
     * Delete item
     * @param Dvelum_Shop_Goods $item
     * @return boolean
     */
    abstract public function delete(Dvelum_Shop_Goods $item);
}