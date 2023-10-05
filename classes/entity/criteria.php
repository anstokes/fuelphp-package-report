<?php

namespace Anstech\Report\Entity;

use Orm\Model;

class Criteria extends Model
{
    protected static $_properties = [''];

    public static function setProperties($properties)
    {
        static::$_properties = $properties;
    }
}
