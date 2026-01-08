<?php

return [
    /**
     * The Eloquent model that represents users in your application.
     */
    'user_model' => config('auth.providers.users.model'),

    /**
     * The application name.
     */
    'app_name' => config('app.name', 'Laravel Package'),

    /**
     * The supported languages for contact system.
     */
    'supported_languages' => ['en', 'ro'],
];
