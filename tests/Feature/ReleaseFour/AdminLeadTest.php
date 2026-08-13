<?php

namespace Tests\Feature\ReleaseFour;

use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadStatus;
use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_and_user_without_leads_view_cannot_access_admin_leads(): void
    {
        $this->get('/admin/leads')->assertRedirect('/admin/login');

        $contentManager = $this->userWithRole(RoleName::ContentManager);
        $this->actingAs($contentManager)->get('/admin/leads')->assertForbidden();
    }

    public function test_sales_manager_can_list_and_view_leads_but_cannot_create_or_delete(): void
    {
        $manager = $this->userWithRole(RoleName::SalesManager);
        $lead = Lead::factory()->create();

        $this->actingAs($manager)
            ->get('/admin/leads')
            ->assertOk();
        $this->get("/admin/leads/{$lead->id}")
            ->assertOk()
            ->assertSee($lead->phone);

        $this->assertFalse(Gate::forUser($manager)->allows('create', Lead::class));
        $this->assertFalse(Gate::forUser($manager)->allows('delete', $lead));
    }

    public function test_sales_manager_can_change_status_comment_and_assignment(): void
    {
        $manager = $this->userWithRole(RoleName::SalesManager);
        $assignee = $this->userWithRole(RoleName::SalesManager);
        $lead = Lead::factory()->create();

        Livewire::actingAs($manager)
            ->test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->fillForm([
                'status' => LeadStatus::Contacted->value,
                'assigned_to' => $assignee->id,
                'manager_comment' => 'Клиенту позвонили.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $lead->refresh();
        $this->assertSame(LeadStatus::Contacted, $lead->status);
        $this->assertSame($assignee->id, $lead->assigned_to);
        $this->assertSame('Клиенту позвонили.', $lead->manager_comment);
    }

    public function test_user_without_leads_update_has_read_only_access(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo([
            PermissionName::AdminAccess->value,
            PermissionName::LeadsView->value,
        ]);
        $lead = Lead::factory()->create();

        $this->actingAs($viewer)->get('/admin/leads')->assertOk();
        $this->get("/admin/leads/{$lead->id}")->assertOk();
        $this->get("/admin/leads/{$lead->id}/edit")->assertForbidden();

        Livewire::actingAs($viewer)
            ->test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->assertActionHidden('edit');
    }

    public function test_invalid_status_and_ineligible_assignee_are_not_saved(): void
    {
        $manager = $this->userWithRole(RoleName::SalesManager);
        $viewer = $this->userWithRole(RoleName::Viewer);
        $lead = Lead::factory()->create();

        Livewire::actingAs($manager)
            ->test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->fillForm(['status' => 'invented-status'])
            ->call('save')
            ->assertHasFormErrors(['status']);

        Livewire::actingAs($manager)
            ->test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->set('data.assigned_to', $viewer->id)
            ->call('save')
            ->assertHasErrors(['data.assigned_to']);

        $lead->refresh();
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertNull($lead->assigned_to);
    }

    public function test_manager_comment_and_message_are_escaped_in_admin_detail(): void
    {
        $manager = $this->userWithRole(RoleName::SalesManager);
        $lead = Lead::factory()->create([
            'message' => '<script>alert("message")</script>',
            'manager_comment' => '<img src=x onerror=alert("comment")>',
        ]);

        $this->actingAs($manager)
            ->get("/admin/leads/{$lead->id}")
            ->assertOk()
            ->assertDontSee($lead->message, false)
            ->assertDontSee($lead->manager_comment, false)
            ->assertSee('&lt;script&gt;', false)
            ->assertSee('&lt;img src=x', false);
    }

    private function userWithRole(RoleName $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }
}
