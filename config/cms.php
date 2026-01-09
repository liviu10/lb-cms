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
     * The application url.
     */
    'app_url' => config('app.url'),

    /**
     * The supported languages for contact system.
     */
    'supported_languages' => ['en', 'ro'],
];
