<?php

namespace Database\Factories;

use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadFormType;
use App\Domain\Leads\LeadSource;
use App\Domain\Leads\LeadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lead> */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+7900'.fake()->numerify('#######'),
            'email' => fake()->safeEmail(),
            'message' => fake()->sentence(),
            'source' => LeadSource::Home,
            'form_type' => LeadFormType::Generic,
            'page_url' => 'http://localhost/',
            'status' => LeadStatus::New,
            'consent_given_at' => now(),
        ];
    }
}
