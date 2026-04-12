<?php

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Message;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageSidebarDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_data_returns_all_active_contacts_even_without_existing_conversations(): void
    {
        $staffRole = Role::create([
            'name' => 'staff',
            'description' => 'Staff',
        ]);

        $cabinet = Cabinet::create([
            'name' => 'Kabinet Sentra Sinergi',
            'year' => '2026/2027',
            'status' => 'active',
        ]);

        $department = Department::create([
            'name' => 'Media',
            'description' => 'Departemen media',
            'cabinet_id' => $cabinet->id,
            'status' => 'active',
        ]);

        $currentUser = User::factory()->createOne([
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        $contactWithoutConversation = User::factory()->createOne([
            'name' => 'Alpha Contact',
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        $contactWithConversation = User::factory()->createOne([
            'name' => 'Beta Contact',
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        User::factory()->createOne([
            'name' => 'Inactive Contact',
            'role_id' => $staffRole->id,
            'department_id' => $department->id,
            'status' => 'inactive',
        ]);

        Message::create([
            'sender_id' => $contactWithConversation->id,
            'receiver_id' => $currentUser->id,
            'content' => 'Halo',
            'is_read' => false,
        ]);

        /** @var User $currentUser */
        $response = $this->actingAs($currentUser)->getJson(route('messages.sidebar-data'));

        $response->assertOk();
        $response->assertJsonCount(2, 'users');
        $response->assertJsonPath('users.0.name', 'Alpha Contact');
        $response->assertJsonFragment(['name' => 'Beta Contact']);
        $response->assertJsonMissing(['name' => 'Inactive Contact']);
        $response->assertJsonCount(1, 'conversations');

        $this->assertSame($contactWithoutConversation->id, $response->json('users.0.id'));
    }
}
