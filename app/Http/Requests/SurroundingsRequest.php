<?php

namespace App\Http\Requests;

use App\Domain\Genplan\SurroundingCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SurroundingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', new Enum(SurroundingCategory::class)],
        ];
    }

    public function category(): ?SurroundingCategory
    {
        $value = $this->validated('category');

        return is_string($value) ? SurroundingCategory::from($value) : null;
    }
}
