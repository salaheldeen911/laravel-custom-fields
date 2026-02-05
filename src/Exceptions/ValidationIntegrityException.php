<?php

namespace Salah\LaravelCustomFields\Exceptions;

use RuntimeException;

class ValidationIntegrityException extends RuntimeException
{
    public static function unvalidatedData(): self
    {
        return new self(
            'Custom field data must be validated using Model::customFieldsValidation($request) or via ValidatesFieldData trait before storage. '.
            "This strict check ensures data integrity and can be disabled in config/custom-fields.php by setting 'strict_validation' to false."
        );
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => 'VALIDATION_INTEGRITY_FAILURE',
                'hint' => 'Ensure you are calling the validation method before saving custom fields.',
            ], 500);
        }
    }
}
