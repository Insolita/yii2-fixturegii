<?php
/**
 * Created by solly [27.05.17 1:10]
 */

namespace insolita\fixturegii\contracts;

/**
 * Class FakerColumnResolver
 *
 * @package insolita\fixturegii\services
 */
interface IFakerColumnResolver
{
    /**
     * @param \yii\db\ColumnSchema[] $columns
     *
     * @return array
     */
    public function buildFakerColumnsData();
}