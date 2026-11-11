<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBulkActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_activate_selected_users(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create(['role' => 'viewer', 'is_active' => false]);

        $response = $this->actingAs($superAdmin)->post(route('users.bulk-action'), [
            'action' => 'activate',
            'user_ids' => [$user->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '1 user(s) activated successfully!');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => true]);
        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'action' => 'activated',
        ]);
    }

    public function test_super_admin_can_deactivate_selected_users(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create(['role' => 'viewer', 'is_active' => true]);

        $response = $this->actingAs($superAdmin)->post(route('users.bulk-action'), [
            'action' => 'deactivate',
            'user_ids' => [$user->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '1 user(s) deactivated successfully!');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'action' => 'deactivated',
        ]);
    }

    public function test_super_admin_can_delete_selected_users(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($superAdmin)->post(route('users.bulk-action'), [
            'action' => 'delete',
            'user_ids' => [$user->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '1 user(s) deleted successfully!');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'action' => 'deleted',
        ]);
    }

    public function test_bulk_action_rejects_the_primary_admin_without_changing_users(): void
    {
        $primaryAdmin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create(['role' => 'viewer', 'is_active' => false]);

        $response = $this->actingAs($primaryAdmin)->post(route('users.bulk-action'), [
            'action' => 'activate',
            'user_ids' => [$primaryAdmin->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', "You cannot perform bulk actions on {$primaryAdmin->name}.");
        $this->assertDatabaseHas('users', ['id' => $primaryAdmin->id, 'is_active' => true]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_bulk_action_rejects_super_admin_targets_for_an_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->post(route('users.bulk-action'), [
            'action' => 'deactivate',
            'user_ids' => [$superAdmin->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', "You cannot perform bulk actions on {$superAdmin->name}.");
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id, 'is_active' => true]);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_bulk_action_requires_a_selected_user(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->from(route('users.index'))->post(route('users.bulk-action'), [
            'action' => 'activate',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasErrors('user_ids');
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_bulk_action_rejects_non_admin_users(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $user = User::factory()->create(['role' => 'viewer', 'is_active' => false]);

        $response = $this->actingAs($viewer)->post(route('users.bulk-action'), [
            'action' => 'activate',
            'user_ids' => [$user->id],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}
