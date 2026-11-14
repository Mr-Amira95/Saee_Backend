<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WhatsAppLog;
use App\Services\WhatsappWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppInboxReplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'whatsapp.api_url'   => 'https://graph.example.test/v20.0',
            'whatsapp.api_token' => 'test-token',
            'whatsapp.sender'    => '12345',
        ]);
    }

    private function inbound(string $phone, string $message, $at = null): WhatsAppLog
    {
        $log = WhatsAppLog::create([
            'phone'        => $phone,
            'message'      => $message,
            'status'       => 'received',
            'direction'    => 'inbound',
            'message_type' => 'text',
            'meta'         => ['profile_name' => 'Walk-in Sender'],
        ]);

        if ($at) {
            $log->forceFill(['created_at' => $at])->save();
        }

        return $log;
    }

    public function test_admin_can_reply_within_the_24_hour_window()
    {
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.ABC']]], 200)]);

        $admin = User::factory()->create(['role' => 'superadmin', 'name' => 'Support Agent']);
        $this->actingAs($admin);
        $this->inbound('962791112222', 'Hi, where is my parcel?');

        $this->post(route('admin.whatsapp-logs.send', '962791112222'), ['message' => 'On its way today!'])
            ->assertRedirect(route('admin.whatsapp-logs.show', '962791112222'));

        Http::assertSent(fn ($request) => $request->url() === 'https://graph.example.test/v20.0/12345/messages'
            && $request['to'] === '962791112222'
            && $request['type'] === 'text'
            && $request['text']['body'] === 'On its way today!');

        $reply = WhatsAppLog::where('direction', 'outbound')->sole();
        $this->assertSame('sent', $reply->status);
        $this->assertSame('wamid.ABC', $reply->meta['wamid']);
        $this->assertSame($admin->id, $reply->meta['sent_by']);

        $this->get(route('admin.whatsapp-logs.index'))
            ->assertOk()
            ->assertSee('Walk-in Sender')
            ->assertDontSee('id="waForm"', false);

        $this->get(route('admin.whatsapp-logs.show', '962791112222'))
            ->assertOk()
            ->assertSee('Walk-in Sender')
            ->assertSee('On its way today!')
            ->assertSee('Support Agent')
            ->assertSee('id="waForm"', false);
    }

    public function test_reply_is_blocked_after_the_24_hour_window()
    {
        Http::fake();

        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $this->inbound('962791112222', 'Old message', now()->subHours(25));

        $this->get(route('admin.whatsapp-logs.show', '962791112222'))
            ->assertOk()
            ->assertDontSee('id="waForm"', false);

        $this->post(route('admin.whatsapp-logs.send', '962791112222'), ['message' => 'Too late'])
            ->assertSessionHas('wa_error');

        Http::assertNothingSent();
        $this->assertSame(0, WhatsAppLog::where('direction', 'outbound')->count());
    }

    public function test_failed_send_is_logged_and_reported()
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'Recipient not on WhatsApp']], 400)]);

        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $this->inbound('962791112222', 'Hello');

        $this->post(route('admin.whatsapp-logs.send', '962791112222'), ['message' => 'Reply'])
            ->assertSessionHas('wa_error');

        $reply = WhatsAppLog::where('direction', 'outbound')->sole();
        $this->assertSame('failed', $reply->status);
        $this->assertSame('Recipient not on WhatsApp', $reply->meta['error']);
    }

    public function test_sending_to_an_unknown_phone_is_not_found()
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));

        $this->post(route('admin.whatsapp-logs.send', '962790000000'), ['message' => 'Hi'])->assertNotFound();
    }

    public function test_admin_can_start_a_new_chat_with_a_template()
    {
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.TPL']]], 200)]);

        $admin = User::factory()->create(['role' => 'superadmin', 'name' => 'Support Agent']);
        $this->actingAs($admin);

        $this->post(route('admin.whatsapp-logs.template'), [
            'phone'    => '+962 79 555 6666',
            'template' => 'support_follow_up',
            'params'   => ["Sami\nAhmad"],
        ])->assertRedirect(route('admin.whatsapp-logs.show', '962795556666'));

        Http::assertSent(fn ($request) => $request['to'] === '962795556666'
            && $request['type'] === 'template'
            && $request['template']['name'] === 'support_follow_up'
            && $request['template']['language']['code'] === 'ar'
            && $request['template']['components'][0]['parameters'][0]['text'] === 'Sami Ahmad');

        $log = WhatsAppLog::sole();
        $this->assertSame('template', $log->message_type);
        $this->assertSame($admin->id, $log->meta['sent_by']);
        $this->assertSame('wamid.TPL', $log->meta['wamid']);

        // No reply from the contact yet, so the chat stays template-only.
        $this->get(route('admin.whatsapp-logs.show', '962795556666'))
            ->assertOk()
            ->assertSee('مرحباً Sami Ahmad')
            ->assertSee('Support Agent')
            ->assertSee('data-wa-template-open', false)
            ->assertDontSee('id="waForm"', false);
    }

    public function test_template_fields_are_required()
    {
        Http::fake();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));

        $this->post(route('admin.whatsapp-logs.template'), [
            'phone'    => '0795556666',
            'template' => 'support_follow_up',
            'params'   => [''],
        ])->assertSessionHasErrors('params.0');

        $this->post(route('admin.whatsapp-logs.template'), [
            'phone'    => '0795556666',
            'template' => 'not_configured',
            'params'   => ['Sami'],
        ])->assertSessionHasErrors('template');

        Http::assertNothingSent();
    }

    public function test_webhook_keeps_the_whatsapp_profile_name()
    {
        app(WhatsappWebhookService::class)->handleIncomingMessage([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'contacts' => [['wa_id' => '962793334444', 'profile' => ['name' => 'Sami']]],
                        'messages' => [['from' => '962793334444', 'type' => 'text', 'text' => ['body' => 'Hello']]],
                    ],
                ]],
            ]],
        ]);

        $this->assertSame('Sami', WhatsAppLog::sole()->meta['profile_name']);
    }
}
