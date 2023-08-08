<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands\Concerns;

use Closure;
use Illuminate\Support\Facades\Validator;

trait CanValidateInput
{
    protected function askRequired(string $question, string $field, ?string $default = null): string
    {
        return $this->validateInput(fn () => $this->ask($question, $default), $field, ['required']);
    }

    /**
     * @param  array<array-key>  $rules
     */
    protected function validateInput(Closure $askUsing, string $field, array $rules, ?Closure $onError = null): string
    {
        $input = $askUsing();

        $validator = Validator::make(
            [$field => $input],
            [$field => $rules],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            if ($onError) {
                $onError($validator);
            }

            $input = $this->validateInput($askUsing, $field, $rules);
        }

        return $input;
    }
}
