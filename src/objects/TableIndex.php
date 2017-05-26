<?php
/**
 * Created by solly [26.05.17 4:55]
 */

namespace insolita\fixturegii\objects;

use yii\base\Object;

/**
 * Class TableIndex
 *
 * @package insolita\fixturegii\objects
 */
class TableIndex extends Object
{
    /**
     * @var
     */
    private $name;
    
    /**
     * @var
     */
    private $columns;
    
    /**
     * @var bool
     */
    private $unique;
    
    /**
     * TableIndex constructor.
     *
     * @param  string $name
     * @param  array  $columns
     * @param bool    $unique
     */
    public function __construct($name, $columns, $unique = false, $config=[])
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->unique = $unique;
        parent::__construct($config);
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }
    
    /**
     * @return bool
     */
    public function isUnique()
    {
        return $this->unique;
    }
    
    /**
     * @return bool
     */
    public function isMultiColumn()
    {
        return (count($this->columns) > 1);
    }
    
}
