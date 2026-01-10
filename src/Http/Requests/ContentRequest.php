<?php

namespace LiviuVoica\LbCms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use LiviuVoica\LbCms\Enums\ContentType;
use LiviuVoica\LbCms\Enums\ContentVisibility;

class ContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentRouteName = Route::current() ? Route::current()->getName() : null;
        $rules = [];

        if ($currentRouteName === 'admin.management.cms.contents.store') {
            $rules = [
                'content_category_id'   => 'required|exists:content_categories,id',
                'visibility' => ['required', Rule::in(array_column(ContentVisibility::cases(), 'value'))],
                'type' => ['required', Rule::in(array_column(ContentType::cases(), 'value'))],
                'scheduled_on' => 'sometimes|date|after:today',
                'slug' => 'required|unique:contents|string|min:5|max:150|regex:/^[a-zA-Z0-9\/-]+$/',
                'tags' => 'required|array',
                'tags.*' => 'string|min:5|max:50|regex:/^[A-Za-z0-9 _-]+$/',
                'title' => 'required|string|min:5|max:120|regex:/^[A-Za-z0-9 .,;:!?\'"\-\(\)&\/]+$/',
                'content' => [
                    'sometimes',
                    'string',
                    'min:300',
                    'max:65535',
                    function ($attribute, $value, $fail) {
                        $pattern = '/<script\b|<iframe\b|<object\b|<embed\b|\bon\w+\s*=|javascript:|data:text\/javascript|expression\([^\)]*\)|<\?(php)?/i';
                        if (preg_match($pattern, $value)) {
                            $fail('content.no_scripts_allowed');
                        }
                    },
                ],
            ];
        }

        if ($currentRouteName === 'admin.management.cms.contents.update') {
            $contentId = $this->route('content')->id;

            $rules = [
                'content_category_id'   => 'sometimes|exists:content_categories,id',
                'visibility' => ['sometimes', Rule::in(array_column(ContentVisibility::cases(), 'value'))],
                'type' => ['sometimes', Rule::in(array_column(ContentType::cases(), 'value'))],
                'scheduled_on' => 'sometimes|date|after:today',
                'slug' => [
                    'sometimes',
                    'string',
                    'min:5',
                    'max:150',
                    'regex:/^[a-z0-9-]+$/',
                    Rule::unique('contents')->ignore($contentId),
                ],
                'tags' => 'sometimes|array',
                'tags.*' => 'string|min:5|max:50|regex:/^[A-Za-z0-9 _-]+$/',
                'title' => 'sometimes|string|min:5|max:120|regex:/^[A-Za-z0-9 .,;:!?\'"\-\(\)&\/]+$/',
                'content' => [
                    'sometimes',
                    'string',
                    'min:300',
                    'max:65535',
                    function ($attribute, $value, $fail) {
                        $pattern = '/<script\b|<iframe\b|<object\b|<embed\b|\bon\w+\s*=|javascript:|data:text\/javascript|expression\([^\)]*\)|<\?(php)?/i';
                        if (preg_match($pattern, $value)) {
                            $fail('content.no_scripts_allowed');
                        }
                    },
                ],
            ];
        }

        return $rules;
    }

    /**
     * Return custom validation messages for the contact subject form fields.
     *
     * @return array<string, array<string,string>|string|null> An associative array of validation rule keys and their messages.
     */
    public function messages(): array
    {
        return [
            'content_category_id.required' => __('translations.validations.content_category_id.required'),
            'content_category_id.exists' => __('translations.validations.content_category_id.exists'),
            'visibility.required' => __('translations.validations.visibility.required'),
            'visibility.in' => __('translations.validations.visibility.in'),
            'type.required' => __('translations.validations.type.required'),
            'type.in' => __('translations.validations.type.in'),
            'scheduled_on.date' => __('translations.validations.scheduled_on.date'),
            'scheduled_on.after' => __('translations.validations.scheduled_on.after'),
            'slug.required' => __('translations.validations.slug.required'),
            'slug.unique' => __('translations.validations.slug.unique'),
            'slug.string' => __('translations.validations.slug.string'),
            'slug.min' => __('translations.validations.slug.min'),
            'slug.max' => __('translations.validations.slug.max'),
            'slug.regex' => __('translations.validations.slug.regex'),
            'tags.required' => __('translations.validations.tags.required'),
            'tags.array' => __('translations.validations.tags.array'),
            'tags.*.string' => __('translations.validations.tags.string'),
            'tags.*.min' => __('translations.validations.tags.min'),
            'tags.*.max' => __('translations.validations.tags.max'),
            'tags.*.regex' => __('translations.validations.tags.regex'),
            'title.required' => __('translations.validations.title.required'),
            'title.string' => __('translations.validations.title.string'),
            'title.min' => __('translations.validations.title.min'),
            'title.max' => __('translations.validations.title.max'),
            'title.regex' => __('translations.validations.title.regex'),
            'content.string' => __('translations.validations.content.string'),
            'content.min' => __('translations.validations.content.min'),
            'content.max' => __('translations.validations.content.max'),
            'content.no_scripts_allowed' => __('translations.validations.content.no_scripts_allowed'),
        ];
    }
}
