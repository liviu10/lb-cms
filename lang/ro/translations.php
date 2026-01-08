<?php

return [
    'validations' => [
        'privacy_policy' => [
            'boolean' => '',
        ],
        'contact_subject_id' => [
            'required' => '',
        ],
        'terms_and_conditions' => [
            'boolean' => '',
        ],
        'is_active' => [
            'required' => '',
            'boolean' => '',
        ],
        'message' => [
            'required' => '',
            'string' => '',
            'min' => '',
            'max' => '',
        ],
        'phone' => [
            'string' => '',
            'min' => '',
            'max' => '',
            'regex' => '',
        ],
        'email' => [
            'required' => '',
            'string' => '',
            'min' => '',
            'max' => '',
            'invalid_format_or_domain' => '',
        ],
        'full_name' => [
            'required' => '',
            'string' => '',
            'min' => '',
            'max' => '',
            'regex' => '',
        ],
        'value' => [
            'required' => '',
            'empty' => '',
            'lang_not_supported' => '',
            'lang_empty' => '',
        ],
    ],
    'mails' => [
        'contact_message_confirmation' => [
            'body' => '',
            'subject' => '',
        ],
        'contact_message_response' => [
            'body' => '',
            'subject' => '',
        ],
    ],
];
