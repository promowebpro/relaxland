@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
])

<label class="form-field">
    <span class="form-field__label">{{ $label }}</span>
    <input
        class="form-field__input"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @required($required)
        {{ $attributes }}
    >
    @error($name)<span class="form-field__error">{{ $message }}</span>@enderror
</label>
