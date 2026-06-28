<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAiChatTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private ClientProfile $clientProfile;
    private City $city;
    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $this->clientUser = User::factory()->create([
            'role' => 'client_master',
            'status' => 'active',
        ]);

        $this->clientProfile = ClientProfile::create([
            'master_user_id' => $this->clientUser->id,
            'company_name' => 'Test Client Company',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);
    }

    public function test_client_ai_chat_renders_with_custom_loader(): void
    {
        $response = $this->actingAs($this->clientUser)->get(route('client.ai-chat.index'));

        $response->assertStatus(200);
        $response->assertSee('saee-delivery-loader');
        $response->assertSee('delivery-van');
        $response->assertSee('delivery-road');
    }
}
