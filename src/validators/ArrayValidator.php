<?php

namespace matrozov\yii2nestedFieldValidation\validators;

use matrozov\yii2nestedFieldValidation\models\DynamicModel;
use matrozov\yii2nestedFieldValidation\traits\ModelValidatorTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Class ArrayValidator
 * @package matrozov\yii2nestedFieldValidation\validators
 *
 * @property array $rules
 */
class ArrayValidator extends Validator
{
    use ModelValidatorTrait;

    public array $rules = [];

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }

        if (empty($this->rules)) {
            throw new InvalidConfigException('"rules" parameter required!');
        }
    }

    /**
     * @param Model  $model
     * @param string $attribute
     *
     * @throws InvalidConfigException
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        $object = new DynamicModel($value);

        static::prepareModelRules($this->rules, $object, $model, $attribute, $value);

        if (!$object->validate()) {
            $errors = $object->getFirstErrors();

            foreach ($errors as $field => $error) {
                if (!preg_match(Html::$attributeRegex, $field, $matches)) {
                    $model->addError($this->formatErrorAttribute($attribute, [$field]), $error);
                    return;
                }

                $model->addError($this->formatErrorAttribute($attribute, [$matches[2]], $matches[3]), $error);
            }
        }

        $model->$attribute = $object->getAttributes();
    }

    /**
     * @param mixed $value
     *
     * @return array|null
     * @throws InvalidConfigException
     */
    public function validateValue($value)
    {
        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            return [$this->message, []];
        }

        $object = new DynamicModel($value);

        static::prepareModelRules($this->rules, $object, new Model(), null, $value);

        if (!$object->validate()) {
            $errors = $object->getFirstErrors();

            foreach ($errors as $field => $error) {
                return [$error, []];
            }
        }

        return null;
    }
}
