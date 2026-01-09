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
                'tags' => 'required|array',
                'tags.*' => 'string|min:5|max:50|regex:/^[A-Za-z0-9 _-]+$/',
                'title' => 'required|string|min:5|max:120|regex:/^[A-Za-z0-9 .,;:!?\'"\-\(\)&\/]+$/',
                'allow_comments' => 'required|boolean',
                'allow_share' => 'required|boolean',
            ];
        }

        if ($currentRouteName === 'admin.management.cms.contents.update') {
            $rules = [
                'content_category_id'   => 'sometimes|exists:content_categories,id',
                'visibility' => ['sometimes', Rule::in(array_column(ContentVisibility::cases(), 'value'))],
                'type' => ['sometimes', Rule::in(array_column(ContentType::cases(), 'value'))],
                'scheduled_on' => 'sometimes|date|after:today',
                'tags' => 'sometimes|array',
                'tags.*' => 'string|min:5|max:50|regex:/^[A-Za-z0-9 _-]+$/',
                'title' => 'sometimes|string|min:5|max:120|regex:/^[A-Za-z0-9 .,;:!?\'"\-\(\)&\/]+$/',
                'allow_comments' => 'sometimes|boolean',
                'allow_share' => 'sometimes|boolean',
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
            'allow_comments.required' => __('translations.validations.allow_comments.required'),
            'allow_comments.boolean' => __('translations.validations.allow_comments.boolean'),
            'allow_share.required' => __('translations.validations.allow_share.required'),
            'allow_share.boolean' => __('translations.validations.allow_share.boolean'),
        ];
    }
}
