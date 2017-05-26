<?php
/**
 * Created by solly [26.05.17 4:55]
 */

namespace insolita\fixturegii\objects;

use yii\helpers\ArrayHelper;

/**
 * Class TableRelation
 *
 * @package insolita\fixturegii\objects
 */
class TableRelation
{
    /**
     * @var string
     */
    private $tableName;
    
    /**
     * @var array
     */
    private $constraints = [];
    
    /**
     * TableRelation constructor.
     *
     * @param string $tableName
     * @param array $constraints
     */
    public function __construct($tableName, $constraints)
    {
        $this->tableName = $tableName;
        foreach ($constraints as $fk => $related) {
            $this->constraints[] = ['fk' => $fk, 'related' => $related];
        }
    }
    
    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }
    
    /**
     * @return array
     */
    public function getConstraints()
    {
        return $this->constraints;
    }
    
    /**
     * @return array
     */
    public function getFk()
    {
        return ArrayHelper::getColumn($this->constraints, 'fk');
    }
    /**
     * @return array
     */
    public function getRelatedIds()
    {
        return ArrayHelper::getColumn($this->constraints, 'pk');
    }
    /**
     * @return bool
     */
    public function isComposite()
    {
        return count($this->constraints) > 1;
    }
}
