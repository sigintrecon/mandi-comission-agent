<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientBulkActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_activate_selected_clients_from_the_clients_form(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $client = $this->createClient('suspended');

        $response = $this->actingAs($superAdmin)->post(route('clients.bulk-action'), [
            'action' => 'activate',
            'client_ids' => [$client->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Activated 1 client(s).');
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'status' => 'active']);
    }

    public function test_super_admin_can_suspend_selected_clients_from_the_clients_form(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $client = $this->createClient('active');

        $response = $this->actingAs($superAdmin)->post(route('clients.bulk-action'), [
            'action' => 'suspend',
            'client_ids' => [$client->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Suspended 1 client(s).');
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'status' => 'suspended']);
    }

    public function test_super_admin_can_delete_selected_clients_from_the_clients_form(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $client = $this->createClient();

        $response = $this->actingAs($superAdmin)->post(route('clients.bulk-action'), [
            'action' => 'delete',
            'client_ids' => [$client->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Deleted 1 client(s).');
        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_bulk_action_requires_client_ids(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)
            ->from(route('clients.index'))
            ->post(route('clients.bulk-action'), ['action' => 'activate']);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHasErrors('client_ids');
    }

    private function createClient(string $status = 'pending'): Client
    {
        return Client::create([
            'name' => fake()->company(),
            'admin_email' => fake()->unique()->safeEmail(),
            'admin_password' => 'password',
            'status' => $status,
        ]);
    }
}
