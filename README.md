# Yii2 Nested Field Validation 

[[Русский](docs/README.ru.md)]

A set of validators for verifying data transferred in nested fields. Validators allow you to check data using standard
Yii2 validators for both a nested object and an array of nested objects. Validators can visualize errors in the classic
Yii2 format, in a form visually compatible with json, and in a format compatible with the
[Yii2 Multiple Field Widget](https://github.com/matrozov/yii2-multiple-field-widget) plugin.

```json
{
  "field": [
    {
      "nested-field-1": "value 1",
      "nested-field-2": 25
    },
    {
      "nested-field-1": "value 2",
      "nested-field-2": 0
    }
  ]
}
```

You can easily verify the specified nested array of objects like this:

```php
public function rules(): array
{
    return [
        [['field'], KeyArrayValidator::class, 'rules' => [
            [['nested-field-1'], 'string', 'max' => 255],        
            [['nested-field-2'], 'integer'],        
        ]],
    ];
}
```

Nested validation allows unlimited nesting, as well as all the usual validators, including file validation.

## Installation

```bash
php composer.phar require matrozov/yii2-nested-field-validation "^1.0"
```

or add

```
"matrozov/yii2-nested-field-validation": "^1.0"
```

to the "require" section of your `composer.json` file.

## Usage

We provide validators for all occasions. You can validate both a nested model, an array of nested models,
and the keys of such an array.

All validators have the ability to specify the format of the errors returned. The following two error formats are available:

**Validator::ERROR_FORMAT_URL** - the format used by default. Compatible with the
[Yii2 Multiple Field](https://github.com/matrozov/yii2-multiple-field) plugin for visualizing errors in View.
Nested fields with errors are formed as elements in square brackets:
```
my_field[0][title]
```

**Validator::ERROR_FORMAT_DOT** - field generation format that uses a dot "." as a separator:
```
my_field.0.title
```
Typically used to visualize errors at the API level.

---

The error format can be set globally:
```php
Validator::$globalErrorFormat = Validator::ERROR_FORMAT_DOT;
```
and locally, at the level of any validator, for example:
```php
[['field'], ArrayValidator::class, 'rules' => [...], 'errorFormat' => Validator::ERROR_FORMAT_DOT],
```

### Validation concept

The validation mechanism offers two solutions for validation. Both by explicitly specifying the validation rules within
the rules of the underlying model:

```php
[['field'], KeyArrayValidator::class, 'rules' => [
    [['nested-field'], 'string'],
]],
```

and in the form of placing the rules in a separate model:

```php
// MainModel rules
[['field'], KeyModelValidator::class, 'model' => FieldModel::class],
```
```php
// FieldModel rules
[['nested-field'], 'string'],
```

Taking the rules into a separate model allows you to decompose the processing of nested entities by taking them out of
the main model. Data loading and validation in the nested model occurs in the classic Yii2 way by using the load()
and validate() methods. That is, you can use the usual methods of processing and validating data in the nested model.
Errors in the nested model will be automatically forwarded and converted according to the format in the main model.

### Validator options

#### ArrayValidator & ModelValidator

A group of validators designed to validate a nested object.

```json
{
  "field": {
    "nested-field-1": "value",
    "nested-field-2": 25
  }
}
```

* ArrayValidator

```php
[['field'], ArrayValidator::class, 'rules' => [
    [['nested-field-1'], 'string'],
    [['nested-field-2'], 'integer'],
]],
```

The validator offers the following set of properties:
* **rules** (array) - A set of rules for validating a nested object.

* Model Validator

```php
// MainModel::rules
[['field'], ModelValidator::class, 'model' => FieldModel::class],
```
```php
// FieldModel::rules
[['nested-field-1'], 'string'],
[['nested-field-2'], 'integer'],
```

The validator offers the following set of properties:
* **model** (string|callable) - Model class to validate the nested object. Can be specified as a function, to which the
    model and attribute will be passed and the object to validate is expected to be returned.
* **scenario** (string|callable) - The scenario set for the nested model before calling the load and validate methods.
  Can be specified as a function that will be passed the model and attribute and is expected to return a string
  with the scenario name.
* **strictClass** (bool) - Non-strict checking allows the use of inheritance from the specified class.

#### KeyValidator

```php
[['field'], KeyValidator::class, 'keyRules' => [
    ['string'],
]],
```

Validator for verifying array keys. It does not validate the elements themselves, but only their keys. Quantity and
sequence. When specifying key validation rules, the syntax is similar to the standard EachValidator validator from
the Yii2 package: the validation rule does not list the fields to which the validation rule is applied, but the
validator itself is specified immediately. Unlike the standard EachValidator, KeyValidator allows specifying a
group of validation rules at once.

he validator offers the following set of properties:
* **keyRules** (array) - A set of rules for validating array keys.
* **messageArray** (string|null) - The message text if the value of the specified field is not an array.
* **keyIsIndexed** (bool) - Checking if the passed field value is a sequential array and not an object, eg.
* **messageKeyIsIndexed** (string|null) - Sequential array check error text.
* **min** (int|null) - Minimum number of array elements.
* **messageMin** (string|null) - Error text if the array contains fewer elements than specified.
* **max** (int|null) - Maximum number of array elements.
* **messageMax** (string|null) - Error text if the array contains more elements than specified.

#### KeyValueValidator

```json
{
  "field": [
    "value-1",
    "value-2"
  ]
}
```

Validator for verifying nested array properties. It inherits from KeyValidator and can validate both keys and values ​​at once.

```php
[['field'], KeyValueValidator::class, 'rules' => [
    ['string'],
]],
```

The validator offers the following set of properties:
* **rules** (array) - A set of rules for validating a nested object.

When specifying validation rules, the syntax used is similar to the standard EachValidator validator from the Yii2
package: the validation rule does not list the fields to which the validation rule applies, but the validator itself
is specified immediately. Unlike the standard EachValidator, KeyValueValidator allows for a group of validation rules
to be specified at once.

#### KeyArrayValidator & KeyModelValidator

A group of validators designed to validate a nested array of objects. It also inherits KeyValidator. The validation
mechanism of these validators is a combination of the mechanisms of such validator classes as KeyValidator and
ArrayValidator/ModelValidator for validating models nested in an array.

```json
{
  "field": [
    {
      "nested-field-1": "value",
      "nested-field-2": 25
    }
  ]
}
```

* KeyArrayValidator

```php
[['field'], KeyArrayValidator::class, 'rules' => [
    [['nested-field-1'], 'string'],
    [['nested-field-2'], 'integer'],
]],
```

The validator properties are identical to the KeyValidator and ArrayValidator properties.

* KeyModelValidator

```php
// MainModel::rules
[['field'], KeyModelValidator::class, 'model' => FieldModel::class], 
```

```php
// FieldModel::rules
[['nested-field-1'], 'string'],
[['nested-field-2'], 'integer'],
```

The properties of the validator are identical to the properties of KeyValidator and ModelValidator.

## License

**yii2-nested-field-validation** is released under the MIT License. See the bundled [LICENSE](./LICENSE) for details.
