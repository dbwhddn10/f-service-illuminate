<?php

namespace Dbwhddn10\FService\Illuminate\Providers;

use Dbwhddn10\FService\Service;
use Dbwhddn10\FService\Illuminate\Collection;
use Dbwhddn10\FService\Illuminate\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory as ValidationFactory;

class FServiceProvider extends ServiceProvider
{
    public function boot()
    {
    	Service::setCollectionResolver(function ($items) {

	        return new Collection($items);
    	});
    	Service::setValidationErrorsResolver(function () {

	        $factory = app(ValidationFactory::class);
	        $factory->resolver(function ($tr, array $data, array $rules, array $messages, array $names)
	        {
	            return new Validator($tr, $data, $rules, $messages, $names);
	        });

	        $validator = $factory->make($data, $rules, $messages=[], $names);
	        $validator->passes();

	        return $validator->errors()->all();
    	});
    }
}
