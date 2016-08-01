<?php

namespace LotVantage\Tusmsa;

use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Validator as Factory;

class TusmsaValidator extends Validator
{

    public function __construct(array $response_data)
    {
        if ($response_data['tusmsa_version'] == 1) {
            parent::__construct(
                Factory::getTranslator(), 
                $response_data,
                [
                    'status'            => 'required|in:1',
                    'tusmsa_version'    => 'required|in:1.0',
                    'stories'           => 'array|present',
                    'stories.*.id'        => 'required|string',
                    'stories.*.title'     => 'present|string',
                    'stories.*.url'       => 'present|url',
                    'stories.*.content'   => 'required|string',
                    'stories.*.image_url' => 'present|url',
                    'stories.*.post_type' => 'required|string|in:URL,TEXT,PHOTO',
                ],
                [
                    'in'       => 'The :attribute field does not have an acceptable value.',
                    'array'    => 'The :attribute field must be an array.',
                    'string'   => 'The :attribute field must be a string.',
                    'required' => 'Required field :attribute is missing.',
                    'url'      => 'The :attribute field must be an absolute URL.',
                ]
            );
        } else {
            // This should fail if the above clause(s) fail(s)
            parent::__construct(
                Factory::getTranslator(), 
                $response_data,
                [
                    'tusmsa_version' => 'required|in:1.0',
                ],
                [
                    'in' => 'The :attribute is not a known value.',
                    'required' => 'The :attribute field is required.',
                ]
            );
        }
    }
}
