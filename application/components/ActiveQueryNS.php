<?php
namespace app\components;

use creocoder\nestedsets\NestedSetsQueryBehavior;

class ActiveQueryNS extends ActiveQuery
{
    public function behaviors()
    {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }

    public function sort()
    {
        $this->orderBy('lft ASC, order ASC');
        return $this;
    }
}
