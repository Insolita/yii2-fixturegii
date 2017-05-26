<?php
/**
 * Created by solly [26.05.17 4:21]
 */

namespace insolita\fixturegii\services;

use insolita\fixturegii\contracts\IFakerColumnResolver;
use yii\db\ColumnSchema;
use yii\helpers\StringHelper;

/**
 * Class FakerColumnResolver
 *
 * @package insolita\fixturegii\services
 */
class FakerColumnResolver implements IFakerColumnResolver
{
    
    /**
     * @var \yii\db\ColumnSchema[]
     */
    protected $columns;
    
    /**
     * @var array
     */
    protected $foreignKeys = [];
    
    protected $uniques = [];
    
    /**
     * FakerColumnResolver constructor.
     *
     * @param  array|ColumnSchema[]                              $columns
     * @param array|\insolita\fixturegii\objects\TableRelation[] $relations
     * @param array|\insolita\fixturegii\objects\TableIndex[]    $indexes
     */
    public function __construct($columns, $relations = [], $indexes = [])
    {
        $this->columns = $columns;
        if (!empty($relations)) {
            foreach ($relations as $relation) {
                $this->foreignKeys = array_merge($this->foreignKeys, $relation->getFk());
            }
        }
        if (!empty($indexes)) {
            foreach ($indexes as $index) {
                if ($index->isUnique() && !$index->isMultiColumn()) {
                    $this->uniques[] = reset($index->getColumns());
                }
            }
        }
    }
    
    /**
     * @param \yii\db\ColumnSchema[] $columns
     *
     * @return array
     */
    public function buildFakerColumnsData()
    {
        $fakerColumns = [];
        foreach ($this->columns as $column) {
            $fakerColumns[$column->name] = $this->guessFakeForColumn($column);
        }
        return $fakerColumns;
    }
    
    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function guessFakeForColumn($column)
    {
        $result = '';
        if ($column->autoIncrement === true || in_array($column->name, $this->foreignKeys)) {
            $result = $this->fakeByType('increment');
        } elseif (StringHelper::startsWith($column->dbType, 'enum(')) {
            $result = $this->fakeByType('enum', $column->enumValues);
        } elseif (StringHelper::startsWith($column->dbType, 'set(')) {
            $result = $this->fakeByType('set', $column->enumValues);
        } elseif (StringHelper::startsWith($column->dbType, '_')) {
            $result = $this->fakeByType('array', $column->dbType);
        } elseif ($column->phpType === 'boolean') {
            $result = $this->fakeByType('boolean');
        } elseif (in_array($column->dbType, ['timestamp', 'date', 'time', 'datetime'])) {
            $result = $this->fakeByType($column->dbType);
        } elseif (in_array($column->dbType, ['float', 'decimal', 'double', 'numeric'])) {
            $result = $this->fakeByType($column->dbType, $column->precision);
        } elseif ($column->phpType === 'string') {
            $result = $this->guessStrings($column);
        } elseif ($column->phpType === 'integer') {
            $result = $this->guessIntegers($column);
        } elseif ($column->allowNull) {
            $result = $this->fakeByType('nullable');
        } elseif ($column->defaultValue) {
            $result = $this->fakeByType('defaults', $column->defaultValue);
        }
        if ($column->allowNull && StringHelper::startsWith($result, '$faker->')) {
            $result = str_replace('$faker->', '$faker->optional()->', $result);
        }
        return $result;
    }
    
    /**
     * @param      $type
     * @param null $data
     *
     * @return string
     */
    protected function fakeByType($type, $data = null)
    {
        switch ($type) {
            case 'increment':
                return '($index+1)';
            case 'boolean':
                return '$faker->boolean(50)';
            case 'timestamp':
            case 'datetime':
                return '$faker->dateTimeThisMonth';
            case 'time':
                return '$faker->time()';
            case 'date':
                return '$faker->date()';
            case 'set':
            case 'enum':
                return '$faker->randomElement([' . implode(',', $data) . '])';
            case 'decimal':
            case 'float':
            case 'double':
            case 'numeric':
                return '$faker->randomFloat(' . $data . ')';
            case 'array': {
                $type = preg_replace('~([^A-Za-z])~', '', $data);
                return "'" . $type . "[]'";
            }
            case 'json': {
                return '{}';
            }
            case 'nullable':
                return 'null';
            case 'default':
                return "'" . $data . "'";
            default:
                return '';
        }
    }
    
    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function guessIntegers(ColumnSchema $column)
    {
        $colName = strtolower(str_replace('_', '', $column->name));
        if ($column->size === 1) {
            return '$faker->randomDigit';
        } elseif ($this->isDateTimeColumn($colName)) {
            return '$faker->unixTime';
        } elseif (StringHelper::endsWith($colName, 'id')) {
            return '$faker->randomDigit';
        }elseif ($this->isEnumerableColumn($colName)) {
            return '$faker->randomElements([])';
        }elseif ($this->isPhoneColumn($colName)) {
            return '$faker->phoneNumber';
        } else {
            $size = (!$column->size || $column->size > 11) ? 10 : $column->size;
            return '$faker->randomNumber(' . $size . ')';
        }
    }
    
    /**
     * @param $name
     *
     * @return bool
     */
    protected function isUniqueColumn($name)
    {
        return in_array($name, $this->uniques);
    }
    
    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function guessStrings(ColumnSchema $column)
    {
        $colName = strtolower(str_replace('_', '', $column->name));
        if ($this->isUserNameColumn($colName)) {
            return '$faker->unique()->userName';
        } elseif ($this->isLastnameColumn($colName)) {
            return '$faker->lastName';
        } elseif ($this->isTitleColumn($colName)) {
            return '$faker->unique()->title';
        } elseif ($this->isEmailColumn($colName)) {
            return '$faker->unique()->email';
        } elseif ($this->isSlugColumn($colName)) {
            return '$faker->unique()->slug';
        } elseif ($this->isPhoneColumn($colName)) {
            return '$faker->phoneNumber';
        } elseif ($this->isEnumerableColumn($colName)) {
            return '$faker->randomElements([])';
        } elseif (StringHelper::endsWith($colName, 'ip')) {
            return '$faker->ipv4';
        } elseif ($this->isPathColumn($colName)) {
            return '"/some/path/".$faker->numerify("#####")';
        } elseif ($this->isImageColumn($colName)) {
            return '$faker->imageUrl()';
        } elseif ($this->isLinkColumn($colName)) {
            return '$faker->url';
        }elseif ($this->isHashColumn($colName)) {
            return '$faker->md5';
        } elseif ($this->isAddressColumn($colName)) {
            return '$faker->address';
        } elseif (mb_strpos($colName, 'street') !== false) {
            return '$faker->streetAddress';
        } elseif ($this->isUidColumn($colName)) {
            return '$faker->uuid';
        } elseif ($column->size > 0 && $column->size < 50) {
            return '$faker->word';
        } else {
            return ($column->size) ? '$faker->realText(' . ($column->size - 1) . ')' : '$faker->paragraph()';
        }
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isDateTimeColumn($colName)
    {
        return (mb_strpos($colName, 'create') !== false
            || mb_strpos($colName, 'update') !== false
            || mb_strpos($colName, 'last') !== false
            || mb_strpos($colName, 'modif') !== false
            || mb_strpos($colName, 'time') !== false
            || mb_strpos($colName, 'date') !== false
            || mb_strpos($colName, 'expire') !== false
            || StringHelper::endsWith($colName, 'at') !== false);
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isUserNameColumn($colName)
    {
        return (mb_strpos($colName, 'username') !== false
            || mb_strpos($colName, 'nick') !== false
            || mb_strpos($colName, 'login') !== false
        );
    }
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isLastnameColumn($colName)
    {
        return in_array($colName, ['lastname','surname']);
    }
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isEnumerableColumn($colName)
    {
        return (mb_strpos($colName, 'type') !== false
            || mb_strpos($colName, 'status') !== false
            || mb_strpos($colName, 'state') !== false
            || mb_strpos($colName, 'mode') !== false
        );
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isImageColumn($colName)
    {
        return (mb_strpos($colName, 'avatar') !== false || mb_strpos($colName, 'picture') !== false
            || mb_strpos($colName, 'image') !== false
            || mb_strpos($colName, 'img') !== false
            || mb_strpos($colName, 'cover') !== false
        );
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isPathColumn($colName)
    {
        return (mb_strpos($colName, 'file') !== false || mb_strpos($colName, 'path') !== false);
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isLinkColumn($colName)
    {
        return (mb_strpos($colName, 'url') !== false || mb_strpos($colName, 'link') !== false);
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isHashColumn($colName)
    {
        return (mb_strpos($colName, 'hash') !== false
            || mb_strpos($colName, 'md5') !== false
            || mb_strpos($colName, 'token') !== false
        );
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isEmailColumn($colName)
    {
        return (mb_strpos($colName, 'mail') !== false);
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isPhoneColumn($colName)
    {
        return (
            mb_strpos($colName, 'phone') !== false
            || mb_strpos($colName, 'mobil') !== false
        );
    }
    
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isAddressColumn($colName)
    {
        return (mb_strpos($colName, 'adres') !== false || mb_strpos($colName, 'address') !== false);
    }
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isTitleColumn($colName)
    {
        return (mb_strpos($colName, 'title') !== false || $colName == 'name');
    }
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isSlugColumn($colName)
    {
        return (mb_strpos($colName, 'slug') !== false);
    }
    /**
     * @param $colName
     *
     * @return bool
     */
    protected function isUidColumn($colName)
    {
        return in_array($colName, ['uid','uuid']);
    }
}
