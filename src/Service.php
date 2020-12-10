<?php

namespace Dbwhddn10\Illuminate;

use Illuminate\Support\Collection;
use Illuminate\Validation\Factory as ValidationFactory;

class Service extends \Dbwhddn10\FService\Service
{
    protected function newCollection($items)
    {
        return new Collection($items);
    }

    protected function getValidationErrors($data, $rules, $names)
    {
        $factory = app(ValidationFactory::class);
        $factory->resolver(function ($tr, array $data, array $rules, array $messages, array $names)
        {
            return new Validator($tr, $data, $rules, $messages, $names);
        });

        $validator = $factory->make($data, $rules, $messages=[], $names);
        $validator->passes();

        return $validator->errors()->all();
    }
}
