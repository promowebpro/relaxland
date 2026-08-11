<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @can(\App\Domain\Users\Enums\PermissionName::ContentUpdate->value)
            <div class="mt-6">
                <x-filament::button type="submit">
                    Сохранить
                </x-filament::button>
            </div>
        @endcan
    </form>
</x-filament-panels::page>
