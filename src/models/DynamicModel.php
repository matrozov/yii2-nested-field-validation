<?php

namespace matrozov\yii2nestedFieldValidation\models;

/**
 * Class DynamicModel
 * @package matrozov\yii2nestedFieldValidation\models
 */
class DynamicModel extends \yii\base\DynamicModel
{
    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (!parent::__isset($name)) {
            $this->defineAttribute($name, null);
        }

        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        if (!parent::__isset($name)) {
            $this->defineAttribute($name, $value);
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return true;
    }
}
