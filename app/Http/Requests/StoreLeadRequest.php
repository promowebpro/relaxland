<?php

namespace App\Http\Requests;

use App\Domain\Leads\LeadFormType;
use App\Domain\Leads\LeadSource;
use App\Domain\Leads\PhoneNormalizer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreLeadRequest extends FormRequest
{
    protected $errorBag = 'lead';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => app(PhoneNormalizer::class)->normalize((string) $this->input('phone')),
            ]);
        }

        $this->merge([
            'name' => $this->string('name')->trim()->value() ?: null,
            'email' => $this->string('email')->trim()->lower()->value() ?: null,
            'message' => $this->string('message')->trim()->value() ?: null,
            'page_url' => $this->safePageUrl($this->input('page_url')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'required_if:form_type,visit,consultation', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,18}$/'],
            'email' => ['nullable', 'email', 'max:254'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['required', new Enum(LeadSource::class)],
            'form_type' => ['required', new Enum(LeadFormType::class)],
            'form_heading' => ['nullable', 'string', 'max:120'],
            'page_url' => ['nullable', 'string', 'max:2048'],
            'consent' => ['accepted'],
            'website' => [
                'nullable',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (filled($value)) {
                        $fail('Не удалось отправить форму.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Укажите телефон.',
            'phone.regex' => 'Проверьте формат телефона.',
            'name.required_if' => 'Укажите имя для записи.',
            'email.email' => 'Укажите корректный email.',
            'consent.accepted' => 'Подтвердите согласие на обработку персональных данных.',
        ];
    }

    private function safePageUrl(mixed $value): ?string
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        $parts = parse_url($value);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && strcasecmp($parts['host'], $this->getHost()) !== 0) {
            return null;
        }

        $path = '/'.ltrim($parts['path'] ?? '/', '/');

        return $this->getSchemeAndHttpHost().$path;
    }
}
