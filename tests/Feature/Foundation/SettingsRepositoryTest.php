<?php

namespace Tests\Feature\Foundation;

use App\Domain\Settings\SettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_typed_settings_are_persisted_updated_and_cached_safely(): void
    {
        $settings = app(SettingsRepository::class);

        $settings->set('contacts.phone', '+7 000 000-00-00', 'contacts', true);
        $settings->set('site.features', ['blog' => true], 'site');

        $this->assertSame('+7 000 000-00-00', $settings->get('contacts.phone'));
        $this->assertSame(['blog' => true], $settings->get('site.features'));
        $this->assertSame('fallback', $settings->get('missing.key', 'fallback'));

        $settings->set('contacts.phone', '+7 111 111-11-11', 'contacts', true);

        $this->assertSame('+7 111 111-11-11', $settings->get('contacts.phone'));
        $this->assertDatabaseCount('settings', 2);
        $this->assertDatabaseHas('settings', [
            'key' => 'contacts.phone',
            'type' => 'string',
            'group' => 'contacts',
            'is_public' => true,
        ]);
    }
}
