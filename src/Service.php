<?php

namespace Illuminate\Extend;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Extend\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Factory as ValidationFactory;

class Service {

    const BIND_NAME_EXP = '/\{\{([a-z0-9\_\.\*]+)\}\}/';

    protected $childs;
    protected $data;
    protected $errors;
    protected $inputs;
    protected $names;
    protected $processed;
    protected $validated;

    public function __construct(array $inputs = [], array $names = [], $validated = [])
    {
        $this->childs    = new Collection;
        $this->data      = new Collection;
        $this->errors    = new Collection;
        $this->inputs    = new Collection($inputs);
        $this->names     = new Collection($names);
        $this->validated = new Collection(array_fill_keys($validated, true));
        $this->processed = false;

        foreach ( $validated as $value )
        {
            $this->data->put($value, $inputs[$value]);
        }

        foreach ( $this->inputs as $key => $value )
        {
            $this->validate($key);
        }
    }

    public function childs()
    {
        return $this->childs;
    }

    public function data()
    {
        $data = $this->data->all();

        ksort($data);

        return new Collection($data);
    }

    public function errors()
    {
        return clone $this->errors;
    }

    public static function getAllBindNames()
    {
        $arr = [];

        foreach ( static::getAllTraits() as $class )
        {
            $arr = array_merge($arr, $class::getArrBindNames());
        }

        $arr = array_merge($arr, static::getArrBindNames());

        return new Collection($arr);
    }

    public static function getAllCallbackLists()
    {
        $arr = [];

        foreach ( static::getAllTraits() as $class )
        {
            $arr = array_merge($arr, $class::getArrCallbackLists());
        }

        $arr = array_merge($arr, static::getArrCallbackLists());

        return new Collection($arr);
    }

    public static function getAllLoaders()
    {
        $arr = [];

        foreach ( static::getAllTraits() as $class )
        {
            $arr = array_merge($arr, $class::getArrLoaders());
        }

        $arr = array_merge($arr, static::getArrLoaders());

        return new Collection($arr);
    }

    public static function getAllPromiseLists()
    {
        $arr = [];

        foreach ( static::getAllTraits() as $class )
        {
            $arr = array_merge_recursive($arr, $class::getArrPromiseLists());
        }

        $arr = array_merge_recursive($arr, static::getArrPromiseLists());

        return new Collection($arr);
    }

    public static function getAllRuleLists()
    {
        $arr = [];

        foreach ( static::getAllTraits() as $class )
        {
            $arr = array_merge_recursive($arr, $class::getArrRuleLists());
        }

        $arr = array_merge_recursive($arr, static::getArrRuleLists());

        return new Collection($arr);
    }

    public static function getAllTraits()
    {
        $arr = [];

        foreach ( static::getArrTraits() as $class )
        {
            $arr = array_merge($arr, $class::getAllTraits()->all());
        }

        $arr = array_merge($arr, static::getArrTraits());
        $arr = array_unique($arr);

        return new Collection($arr);
    }

    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [];
    }

    public static function getArrLoaders()
    {
        return [];
    }

    public static function getArrPromiseLists()
    {
        return [];
    }

    public static function getArrRuleLists()
    {
        return [];
    }

    public static function getArrTraits()
    {
        return [];
    }

    protected function getValidationErrors($data, $ruleList)
    {
        $factory = app(ValidationFactory::class);
        $factory->resolver(function ($tr, array $data, array $rules, array $messages, array $names)
        {
            $class = preg_replace('/Service$/', 'Validator', self::class);
            return new $class($tr, $data, $rules, $messages, $names);
        });

        foreach ( $ruleList as $key => $rules )
        {
            $validator = $factory->make($data->toArray(), [$key => $rules], [], $this->names->toArray());
            $validator->passes();

            if ( !empty($validator->errors()->all()) )
            {
                $errors = $this->errors->get($key, []);
                $errors = array_merge($errors, $validator->errors()->all());

                $this->errors->put($key, $errors);
            }
        }

        return $this->errors->get($key, []);
    }

    public static function initService($value)
    {
        $value = Arr::add($value, 1, []);
        $value = Arr::add($value, 2, []);
        $value = Arr::add($value, 3, []);

        $class  = $value[0];
        $data   = $value[1];
        $names  = $value[2];
        $valids = $value[3];

        foreach ( $data as $key => $value )
        {
            if ( $value === '')
            {
                unset($data[$key]);
            }
        }

        return new $class($data, $names, $valids);
    }

    public function inputs()
    {
        return clone $this->inputs;
    }

    public static function isInitable($value)
    {
        return is_array($value) && array_key_exists(0, $value) && is_string($value[0]) && preg_match('/Service$/', $value[0]);
    }

    protected function isRequiredRule($rule)
    {
        return preg_match('/^required/', $rule);
    }

    protected function isResolveError($value)
    {
        $errorClass = get_class($this->resolveError());

        return is_object($value) && $value instanceof $errorClass;
    }

    protected function getAvailableDataWith($key)
    {
        $key  = explode('.', $key)[0];
        $data = $this->data();

        if ( $this->inputs()->has($key) )
        {
            $value = $this->inputs()->get($key);

            $data->put($key, $value);
        }
        else if ( ! $this->data()->has($key) && $this->getAllLoaders()->has($key) )
        {
            $loader = $this->getAllLoaders()->get($key);
            $value  = $this->resolve($loader);

            if ( static::isInitable($value) )
            {
                $value = Arr::add($value, 2, []);

                foreach ( $value[2] as $k => $name )
                {
                    $value[2][$k] = $this->resolveBindName($name);
                }

                $service = static::initService($value);
                $value   = $service->run();

                $this->childs->put($key, $service);
            }

            if ( ! $this->isResolveError($value) )
            {
                $data->put($key, $value);
            }
        }

        return $data;
    }

    protected function getAvailableRulesWith($key)
    {
        $rules   = $this->getAllRuleLists()->get($key, []);
        $mainKey = explode('.', $key)[0];

        if ( ! $this->getAllLoaders()->has($mainKey) && ! $this->inputs->has($mainKey) )
        {
            $rules = array_filter($rules, function ($rule) {
                return $this->isRequiredRule($rule);
            });
        }

        if ( empty($rules) )
        {
            return [];
        }

        $this->names[$key] = $this->resolveBindName('{{'.$key.'}}');

        foreach ( $rules as $i => $rule )
        {
            $bindKeys = $this->getBindKeys($rule);

            foreach ( $bindKeys as $bindKey )
            {
                $this->names[$bindKey] = $this->resolveBindName('{{'.$bindKey.'}}');

                if ( ! $this->validate($bindKey) )
                {
                    $this->validated->put($mainKey, false);

                    unset($rules[$i]);

                    continue;
                }

                if ( ! $this->isRequiredRule($rule) && ! $this->data()->has($bindKey) )
                {
                    throw new \Exception('"' . $bindKey . '" key required rule not exists');
                }
            }

            if ( array_key_exists($i, $rules) )
            {
                $rules[$i] = preg_replace(static::BIND_NAME_EXP, '$1', $rule);
            }
        }

        return array_values($rules);
    }

    protected function getBindKeys(string $str)
    {
        $matches = [];

        preg_match_all(static::BIND_NAME_EXP, $str, $matches);

        return $matches[1];
    }

    protected function getClosureDependencies($func)
    {
        if ( !is_object($func) || !($func instanceof \Closure) )
        {
            return [];
        }

        $deps   = [];
        $params = (new \ReflectionFunction($func))->getParameters();

        foreach ( $params as $i => $param )
        {
            $deps[] = strtolower(
                preg_replace(
                    [
                        '#([A-Z][a-z]*)(\d+[A-Z][a-z]*\d+)#',
                        '#([A-Z]+\d*)([A-Z])#',
                        '#([a-z]+\d*)([A-Z])#',
                        '#([^_\d])([A-Z][a-z])#'
                    ],
                    '$1_$2',
                    $param->name
                )
            );
        }

        return $deps;
    }

    protected function getPromiseOrderedDependencies($keys)
    {
        $arr  = [];
        $rtn  = [];

        foreach ( $keys as $key )
        {
            $deps = $this->getAllPromiseLists()->get($key, []);
            $list = $this->getPromiseOrderedDependencies($deps);
            $list = array_merge($list, [$key]);
            $arr  = array_merge($list, $arr);
        }

        foreach ( $arr as $value )
        {
            $rtn[$value] = null;
        }

        return array_keys($rtn);
    }

    protected function resolve($func)
    {
        $resolver = \Closure::bind($func, $this);
        $depNames = $this->getClosureDependencies($func);
        $depVals  = [];
        $params   = (new \ReflectionFunction($resolver))->getParameters();

        foreach ( $depNames as $i => $depName )
        {
            if ( $this->data->has($depName) )
            {
                $depVals[] = $this->data->get($depName);
            }
            else if ( $params[$i]->isDefaultValueAvailable() )
            {
                $depVals[] = $params[$i]->getDefaultValue();
            }
            else
            {
                // must not throw exception, but only return
                return $this->resolveError();
            }
        }

        return call_user_func_array($resolver, $depVals);
    }

    protected function resolveBindName(string $name)
    {
        while ( $boundKeys = $this->getBindKeys($name) )
        {
            $key      = $boundKeys[0];
            $pattern  = '/\{\{' . $key . '\}\}/';
            $bindName = $this->getAllBindNames()->merge($this->names)->get($key);

            if ( $bindName == null )
            {
                throw new \Exception('"' . $key . '" name not exists');
            }

            $replace = $this->resolveBindName($bindName);
            $name    = preg_replace($pattern, $replace, $name, 1);
        }

        return $name;
    }

    protected function resolveError()
    {
        return new \Exception('can\'t be resolve');
    }

    public function run()
    {
        if ( ! $this->processed )
        {
            // foreach ( $this->getAllBindNames()->merge($this->names) as $key => $name )
            // {
            //     $this->names[$key] = $this->resolveBindName($name);
            // }

            foreach ( $this->inputs()->keys() as $key )
            {
                $this->validate($key);
            }

            foreach ( $this->getAllRuleLists()->keys() as $key )
            {
                $this->validate(explode('.', $key)[0]);
            }

            foreach ( $this->getAllLoaders()->keys() as $key )
            {
                $this->validate($key);
            }

            $this->processed = true;
        }

        if ( ! $this->totalErrors()->isEmpty() )
        {
            return $this->resolveError();
        }

        if ( ! $this->data()->has('result') )
        {
            throw new \Exception('result data key is not exists in '.static::class);
        }

        return $this->data()->get('result');
    }

    public function totalErrors()
    {
        $errors = $this->errors()->flatten();

        foreach ( $this->childs() as $child )
        {
            $errors = $errors->merge($child->totalErrors());
        }

        return $errors;
    }

    protected function validate($key)
    {
        if ( count(explode('.', $key)) > 1 )
        {
            throw new \Exception('does not support validation with child key');
        }

        if ( $this->validated->has($key) )
        {
            return $this->validated->get($key);
        }

        $promiseList = $this->getAllPromiseLists()->get($key, []);

        foreach ( $promiseList as $promise )
        {
            $promiseKey = explode(':', $promise)[0];
            $isStrict   = explode(':', $promise)[1] == 'strict';

            if ( !$this->validate($promiseKey) && $isStrict )
            {
                $this->validated->put($key, false);

                return false;
            }
        }

        $loader = $this->getAllLoaders()->get($key);
        $deps   = $this->getClosureDependencies($loader);

        foreach ( $deps as $dep )
        {
            if ( !$this->validate($dep) )
            {
                $this->validated->put($key, false);
            }
        }

        if ( $this->validated->get($key) === false )
        {
            return false;
        }

        $ruleList = [$key => $this->getAvailableRulesWith($key)];
        $data     = $this->getAvailableDataWith($key);

        if ( $this->getAllRuleLists()->has($key.'.*') )
        {
            $ruleList[$key.'.*'] = $this->getAvailableRulesWith($key.'.*');
        }

        $errors = $this->getValidationErrors($data, $ruleList);

        if ( ! empty($errors) || ($this->childs->has($key) && ! $this->childs->get($key)->totalErrors()->isEmpty()) )
        {
            $this->validated->put($key, false);

            return false;
        }

        if ( $this->validated->get($key) === false )
        {
            return false;
        }

        if ( $data->has($key) )
        {
            $this->data->put($key, $data->get($key));
        }

        $this->validated->put($key, true);

        $promiseKeys  = $this->getAllPromiseLists()->keys()->filter(function ($value) use ($key) {

            return preg_match('/^'.$key.'\\./', $value);
        })->toArray();
        $callbackKeys = $this->getAllCallbackLists()->keys()->filter(function ($value) use ($key) {

            return preg_match('/^'.$key.'\\./', $value);
        })->toArray();
        $orderedKeys  = $this->getPromiseOrderedDependencies($promiseKeys);
        $restKeys     = array_diff($callbackKeys, $orderedKeys);
        $callbackKeys = array_merge($orderedKeys, $restKeys);

        foreach ( $callbackKeys as $callbackKey )
        {
            $callback = $this->getAllCallbackLists()->get($callbackKey);
            $deps     = $this->getClosureDependencies($callback);

            foreach ( $deps as $dep )
            {
                if ( !$this->validate($dep) )
                {
                    $this->validated->put($key, false);
                }
            }

            $this->resolve($callback);
        }

        if ( $this->validated->get($key) === false )
        {
            return false;
        }

        return true;
    }

    public function validated()
    {
        $arr = $this->validated->all();

        ksort($arr);

        return new Collection($arr);
    }
}
