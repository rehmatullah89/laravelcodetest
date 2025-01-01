<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    /**
     * Validate the given data against the provided rules.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @throws ValidationException
     * @return void
     */
    public function validate(array $data, array $rules, array $messages = [], array $attributes = [])
    {
        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate and return a response with errors if validation fails.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateWithResponse(array $data, array $rules, array $messages = [], array $attributes = [])
    {
        return Validator::make($data, $rules, $messages, $attributes);
    }

    /**
     * Prepare validation rules for job creation.
     *
     * @return array
     */
    public function jobCreationRules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'due_date' => 'required|date|after:today',
            'language_id' => 'required|exists:languages,id',
            'duration' => 'required|integer|min:1',
        ];
    }

    /**
     * Prepare validation rules for updating a job.
     *
     * @return array
     */
    public function jobUpdateRules()
    {
        return [
            'status' => 'required|in:pending,completed,canceled',
            'translator_id' => 'nullable|exists:users,id',
        ];
    }
}

