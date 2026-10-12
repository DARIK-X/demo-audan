<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('manage-portal') === true; }
    protected function prepareForValidation(): void {
        if (!$this->has('is_published')) {
            $school = $this->route('school');
            $this->merge(['is_published' => $school instanceof \App\Models\School ? $school->is_published : true]);
        }
    }
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(config('portal.school_types'))],
            'village' => ['required', 'string', 'max:100'], 'address' => ['required', 'string', 'max:200'],
            'students' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website_url' => ['bail', 'nullable', 'string', 'url:http,https', 'max:2048', function ($attribute, $value, $fail) { if ($value && parse_url($value, PHP_URL_SCHEME) !== 'https') $fail('Тек HTTPS сілтемесі рұқсат етіледі.'); }],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000'],
            'remove_photo' => ['nullable', 'boolean'], 'description' => ['nullable', 'string', 'max:1200'],
            'is_published' => ['required', 'boolean'], 'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
