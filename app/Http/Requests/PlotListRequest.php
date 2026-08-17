<?php

namespace App\Http\Requests;

use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\PlotFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlotListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['mode', 'status', 'area_min', 'area_max', 'price_min', 'price_max', 'sort'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'mode' => ['nullable', Rule::enum(GenplanMode::class)],
            'status' => ['nullable', Rule::in(PlotFilters::publicStatuses())],
            'area_min' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999'],
            'area_max' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999', 'gte:area_min'],
            'price_min' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999'],
            'price_max' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999', 'gte:price_min'],
            'sort' => ['nullable', Rule::in(PlotFilters::sorts())],
        ];
    }

    public function mode(): GenplanMode
    {
        $mode = $this->validated('mode');

        return is_string($mode) ? GenplanMode::from($mode) : GenplanMode::default();
    }

    public function filters(): PlotFilters
    {
        return PlotFilters::fromArray($this->validated());
    }
}
