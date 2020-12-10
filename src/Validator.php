<?php

namespace Dbwhddn10\Illuminate;

class Validator extends \Illuminate\Validation\Validator {

    public function __construct($translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct(
            $translator,
            $data,
            $rules,
            $messages,
            $customAttributes
        );

        $this->sizeRules  = array_merge($this->sizeRules, [
            'Less', 'More'
        ]);
        $this->implicitRules  = array_merge($this->implicitRules, [
            // ... add some implicit custom rules
        ]);
        $this->dependentRules = array_merge($this->dependentRules, [
            // ... add some dependent custom rules
        ]);
    }

    public function addError($attribute, $rule, $parameters)
    {
        return parent::addError($attribute, $rule, $parameters);
    }

    protected function getCustomAttribute($key)
    {
        if ( array_key_exists($key, $this->customAttributes) )
        {
            return $this->customAttributes[$key];
        }

        return $key;
    }

    public function getDisplayableAttribute($attribute)
    {
        $pAttr        = $this->getPrimaryAttribute($attribute);
        $asteriskKeys = $this->getExplicitKeys($attribute);
        $cAttr        = $this->getCustomAttribute($pAttr);
        $matches      = [];

        if ( $asteriskKeys != [] )
        {
            $cAttr = str_replace('*', $asteriskKeys[0], $cAttr);
        }

        return $cAttr;
    }

    protected function replaceLess($message, $attribute, $rule, $parameters)
    {
        return str_replace(':less', $parameters[0], $message);
    }

    protected function replaceMore($message, $attribute, $rule, $parameters)
    {
        return str_replace(':more', $parameters[0], $message);
    }

    protected function replaceSeveralIn($message, $attribute, $rule, $parameters)
    {
        $options = $this->getValue($parameters[0]);
        $options = implode(',', $options);

        return str_replace(':list', $options, $message);
    }

    public function validateBase64($attribute, $value, $parameters, $validator)
    {
        if ( base64_encode(base64_decode($value, true)) === $value )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function validateBase64Image($attribute, $value, $parameters, $validator)
    {
        $image = base64_decode($value);
        $f = finfo_open();
        $mime = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
        finfo_close($f);

        return in_array($mime, ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    public function validateDate($attribute, $value)
    {
        return is_string($value) && preg_match('/^(19|20)(\\d{2})-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\\d|3[0-1])$/', $value);
    }

    public function validateFalse($attribute, $value, $parameters, $validator)
    {
        return $value === false || $value === 'false' || $value === 0 || $value === '0';
    }

    public function validateInIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'in_if');

        $parameters[0] = $this->getValue($parameters[0]);

        if ( $parameters[0] != $parameters[1] )
        {
            return true;
        }

        $parameters = array_slice($parameters, 2);

        return $this->validateIn($attribute, $value, $parameters);
    }

    public function validateIntegers($attribute, $value)
    {
        if ( ! $this->validateString($attribute, $value) && ! $this->validateInteger($attribute, $value) )
        {
            return false;
        }

        $integers = preg_split('/\s*,\s*/', $value);
        $result = true;

        foreach ( $integers as $integer )
        {
            ! $this->validateInteger($attribute, $integer) ?
                $result = false : null;
        }

        return $result;
    }

    public function validateInUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'in_less');

        $parameters[0] = $this->getValue($parameters[0]);

        if ( $parameters[0] == $parameters[1] )
        {
            return true;
        }

        $parameters = array_slice($parameters, 2);

        return $this->validateIn($attribute, $value, $parameters);
    }

    public function validateLess($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'less');

        if ($value instanceof UploadedFile && ! $value->isValid()) {
            return false;
        }

        return $this->getSize($attribute, $value) < $parameters[0];
    }

    public function validateMore($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'more');

        return $this->getSize($attribute, $value) > $parameters[0];
    }

    public function validateNotNull($attribute, $value)
    {
        return ! $this->validateNull($attribute, $value);
    }

    public function validateNotNullIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'not_null_if');

        $ifValue = $this->getValue($parameters[0]);

        if ( $ifValue != $parameters[1] )
        {
            return true;
        }

        return $this->validateNotNull($attribute, $value);
    }

    public function validateNotNullUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'not_null_unless');

        $ifValue = $this->getValue($parameters[0]);

        if ( $ifValue == $parameters[1] )
        {
            return true;
        }

        return $this->validateNotNull($attribute, $value);
    }

    public function validateNull($attribute, $value)
    {
        if ( is_null($value) )
        {
            return true;
        }

        return false;
    }

    public function validateNullIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'null_if');

        $ifValue = $this->getValue($parameters[0]);

        if ( $ifValue != $parameters[1] )
        {
            return true;
        }

        return $this->validateNull($attribute, $value);
    }

    public function validateNullUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'null_unless');

        $ifValue = $this->getValue($parameters[0]);

        if ( $ifValue == $parameters[1] )
        {
            return true;
        }

        return $this->validateNull($attribute, $value);
    }

    public function validateMax($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'max');

        $limit = $parameters[0];

        if ( array_key_exists($limit, $this->data) )
        {
            $parameters[0] = $this->getValue($limit);
        }

        return parent::validateMax($attribute, $value, $parameters);
    }

    public function validateMin($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'min');

        $limit = $parameters[0];

        if ( array_key_exists($limit, $this->data) )
        {
            $parameters[0] = $this->getValue($limit);
        }

        return parent::validateMin($attribute, $value, $parameters);
    }

    public function validateRequiredIfContain($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_if_contain');

        $search = $this->getValue($parameters[0]);
        $string = $parameters[1];

        if ( strpos($search, $string) !== false )
        {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    public function validateSeveralIn($attribute, $value, $parameters, $validator)
    {
        $this->requireParameterCount(1, $parameters, 'several_in');

        $isValid = true;
        $value   = preg_split('/\s*,\s*/', $value);
        $options = $this->getValue($parameters[0]);

        foreach ( $value as $key )
        {
            if ( ! in_array($key, $options) )
            {
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateTrue($attribute, $value, $parameters, $validator)
    {
        return $value === true || $value === 'true' || $value === 1 || $value === '1';
    }

}
