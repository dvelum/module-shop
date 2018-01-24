<?php
class Dvelum_Shop_Event
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get event data
     * @return array
     */
    public function getData()
    {
        return $this->eventData;
    }
}