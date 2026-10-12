<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ArticleRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('manage-portal') === true; }
    public function rules(): array { return [
        'title' => ['required', 'string', 'max:180'], 'category' => ['required', Rule::in(array_keys(config('portal.categories')))],
        'excerpt' => ['nullable', 'string', 'max:400'], 'body' => ['required', 'string', 'max:30000'],
        'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000'],
        'attachment' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        'remove_image' => ['nullable', 'boolean'], 'remove_attachment' => ['nullable', 'boolean'],
        'is_published' => ['nullable', 'boolean'], 'published_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
    ]; }
}
