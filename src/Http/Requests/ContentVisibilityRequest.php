<?php

namespace LiviuVoica\LbCms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class ContentVisibilityRequest extends FormRequest
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
        $languages = config('cms.supported_languages');
        $rules = [];

        $valueValidator = $this->valueValidationRule($languages);

        if ($currentRouteName === 'admin.management.cms.visibility.store') {
            $rules['value'] = array_merge(['required', 'array'], $valueValidator);
        }

        if ($currentRouteName === 'admin.management.cms.visibility.update') {
            $rules['value'] = array_merge(['sometimes', 'array'], $valueValidator);
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
        $languages = config('cms.supported_languages');

        $messages = [
            'value.required' => __('translations.validations.value.required'),
            'value.empty' => __('translations.validations.value.empty'),
        ];

        foreach ($languages as $lang) {
            $messages["value.lang_not_supported.$lang"] = __('translations.validations.value.lang_not_supported', ['lang' => $lang]);
            $messages["value.lang_empty.$lang"] = __('translations.validations.value.lang_empty', ['lang' => $lang]);
        }

        return $messages;
    }

    /**
     * Validare custom pentru câmpul `value`.
     *
     * @param  array<string>  $languages
     * @return array<int, \Closure>
     */
    private function valueValidationRule(array $languages): array
    {
        return [
            function ($attribute, $value, $fail) use ($languages) {
                if (empty($value)) {
                    $fail('value.empty');

                    return;
                }

                foreach ($value as $lang => $text) {
                    if (! in_array($lang, $languages)) {
                        $fail('value.lang_not_supported.'.$lang);

                        return;
                    }

                    if (! is_string($text) || trim($text) === '') {
                        $fail('value.lang_empty.'.$lang);

                        return;
                    }
                }
            },
        ];
    }
}
