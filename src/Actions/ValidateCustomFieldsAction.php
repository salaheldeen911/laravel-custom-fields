<?php

namespace Salah\LaravelCustomFields\Actions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class ValidateCustomFieldsAction
{
    public function __construct(
        protected SetupValidationRulesAction $setupRulesAction
    ) {}

    public function execute(string $modelClass, array $data, \Closure $onValidated): ValidationValidator
    {
        $rules = $this->setupRulesAction->execute($modelClass);
        $validator = Validator::make($data, $rules);

        $validator->after(function ($validator) use ($onValidated) {
            if (! $validator->errors()->any()) {
                $onValidated();
            }
        });

        return $validator;
    }
}
