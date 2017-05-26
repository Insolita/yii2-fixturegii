<?php
/**
 * Created by solly [25.05.17 12:22]
 */

namespace insolita\fixturegii\contracts;

interface IFakerColumnResolver
{
    public function fakeByColumnType($type,$size);
    
    public function fakeByColumnName($name,$size);
}
