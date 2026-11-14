<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WhatsAppLog;
use App\Services\WhatsappWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WhatsAppMediaTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '962791112222';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'whatsapp.api_url'    => 'https://graph.example.test/v20.0',
            'whatsapp.api_token'  => 'test-token',
            'whatsapp.sender'     => '12345',
            'whatsapp.media_disk' => 'local',
        ]);

        Storage::fake('local');
        // No ffmpeg in tests: voice conversion always "fails".
        Process::fake(['*' => Process::result(exitCode: 1)]);
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'superadmin', 'name' => 'Support Agent']);
        $this->actingAs($admin);

        return $admin;
    }

    /** An inbound message in the last 24h, so replies are allowed. */
    private function openChat(): void
    {
        WhatsAppLog::create([
            'phone' => self::PHONE, 'message' => 'Hi', 'status' => 'received',
            'direction' => 'inbound', 'message_type' => 'text',
        ]);
    }

    private function fakeMetaSend(): void
    {
        Http::fake([
            'graph.example.test/v20.0/12345/media'    => Http::response(['id' => 'media-1']),
            'graph.example.test/v20.0/12345/messages' => Http::response(['messages' => [['id' => 'wamid.OUT']]]),
        ]);
    }

    public function test_admin_can_send_an_image_with_a_caption()
    {
        $this->actingAsAdmin();
        $this->openChat();
        $this->fakeMetaSend();

        $this->post(route('admin.whatsapp-logs.send', self::PHONE), [
            'message' => 'Your parcel',
            'file'    => UploadedFile::fake()->image('photo.jpg', 40, 40),
        ])->assertRedirect(route('admin.whatsapp-logs.show', self::PHONE));

        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/12345/media') && $r->isMultipart());
        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/12345/messages')
            && $r['type'] === 'image'
            && $r['image'] === ['id' => 'media-1', 'caption' => 'Your parcel']);

        $log = WhatsAppLog::where('direction', 'outbound')->sole();
        $this->assertSame('image', $log->message_type);
        $this->assertSame('sent', $log->status);
        Storage::disk('local')->assertExists($log->meta['media']['path']);

        $this->get(route('admin.whatsapp-logs.show', self::PHONE))
            ->assertOk()
            ->assertSee(route('admin.whatsapp-logs.media', $log))
            ->assertSee('Your parcel');

        $this->get(route('admin.whatsapp-logs.media', $log))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_documents_are_sent_with_their_file_name()
    {
        $this->actingAsAdmin();
        $this->openChat();
        $this->fakeMetaSend();

        $this->post(route('admin.whatsapp-logs.send', self::PHONE), [
            'file' => UploadedFile::fake()->create('invoice.pdf', 120, 'application/pdf'),
        ])->assertRedirect();

        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/messages')
            && $r['type'] === 'document'
            && $r['document'] === ['id' => 'media-1', 'filename' => 'invoice.pdf']);

        $this->assertSame('invoice.pdf', WhatsAppLog::where('direction', 'outbound')->sole()->meta['media']['filename']);
    }

    public function test_files_over_the_whatsapp_limit_are_rejected()
    {
        $this->actingAsAdmin();
        $this->openChat();
        Http::fake();

        $this->post(route('admin.whatsapp-logs.send', self::PHONE), [
            'file' => UploadedFile::fake()->image('huge.jpg')->size(6 * 1024),
        ])->assertSessionHas('wa_error');

        Http::assertNothingSent();
    }

    public function test_voice_recording_in_a_supported_format_is_sent_as_audio()
    {
        $this->actingAsAdmin();
        $this->openChat();
        $this->fakeMetaSend();

        $this->post(route('admin.whatsapp-logs.send', self::PHONE), [
            'file'  => UploadedFile::fake()->create('voice-message.ogg', 20, 'audio/ogg'),
            'voice' => '1',
        ])->assertRedirect();

        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/messages')
            && $r['type'] === 'audio' && $r['audio'] === ['id' => 'media-1']);

        $log = WhatsAppLog::where('direction', 'outbound')->sole();
        $this->assertTrue($log->meta['media']['voice']);

        $this->get(route('admin.whatsapp-logs.index'))->assertSee('Voice message');
    }

    public function test_webm_voice_recording_without_ffmpeg_is_rejected()
    {
        $this->actingAsAdmin();
        $this->openChat();
        Http::fake();

        $this->post(route('admin.whatsapp-logs.send', self::PHONE), [
            'file'  => UploadedFile::fake()->create('voice-message.webm', 20, 'audio/webm'),
            'voice' => '1',
        ])->assertSessionHas('wa_error');

        Http::assertNothingSent();
    }

    public function test_incoming_media_is_downloaded_and_stored()
    {
        Http::fake([
            'graph.example.test/v20.0/media-in' => Http::response(['url' => 'https://cdn.example.test/f/1', 'mime_type' => 'image/jpeg']),
            'cdn.example.test/*'                => Http::response('JPEG-BYTES'),
        ]);

        $this->webhook(['id' => 'wamid.IN1', 'from' => self::PHONE, 'type' => 'image',
            'image' => ['id' => 'media-in', 'mime_type' => 'image/jpeg', 'caption' => 'Damaged box']]);

        $log = WhatsAppLog::sole();
        $this->assertSame('image', $log->message_type);
        $this->assertSame('Damaged box', $log->message);
        $this->assertSame('JPEG-BYTES', Storage::disk('local')->get($log->meta['media']['path']));

        $this->actingAsAdmin();
        $this->get(route('admin.whatsapp-logs.index'))->assertSee('📷 Damaged box');
    }

    public function test_failed_incoming_download_is_retried_when_opened()
    {
        Http::fake([
            'graph.example.test/v20.0/media-in' => Http::sequence()
                ->push(['error' => ['message' => 'Temporary']], 500)
                ->push(['url' => 'https://cdn.example.test/f/2', 'mime_type' => 'audio/ogg']),
            'cdn.example.test/*' => Http::response('OGG-BYTES'),
        ]);

        $this->webhook(['id' => 'wamid.IN2', 'from' => self::PHONE, 'type' => 'audio',
            'audio' => ['id' => 'media-in', 'mime_type' => 'audio/ogg; codecs=opus', 'voice' => true]]);

        $log = WhatsAppLog::sole();
        $this->assertArrayHasKey('download_error', $log->meta['media']);

        $this->actingAsAdmin();
        $this->get(route('admin.whatsapp-logs.media', $log))->assertOk();

        $media = $log->fresh()->meta['media'];
        $this->assertArrayNotHasKey('download_error', $media);
        $this->assertSame('OGG-BYTES', Storage::disk('local')->get($media['path']));
    }

    public function test_duplicate_webhook_deliveries_are_saved_once()
    {
        $message = ['id' => 'wamid.DUP', 'from' => self::PHONE, 'type' => 'text', 'text' => ['body' => 'Hello']];

        $this->webhook($message);
        $this->webhook($message);

        $this->assertSame(1, WhatsAppLog::count());
    }

    public function test_delivery_statuses_update_the_sent_message()
    {
        $log = WhatsAppLog::create([
            'phone' => self::PHONE, 'message' => 'Hi', 'status' => 'sent', 'direction' => 'outbound',
            'message_type' => 'text', 'meta' => ['wamid' => 'wamid.S1'],
        ]);

        $this->deliveryStatus('wamid.S1', 'delivered');
        $this->assertSame('delivered', $log->fresh()->status);

        $this->deliveryStatus('wamid.S1', 'sent'); // late, out-of-order update
        $this->assertSame('delivered', $log->fresh()->status);

        $this->deliveryStatus('wamid.S1', 'read');
        $this->assertSame('read', $log->fresh()->status);

        $this->deliveryStatus('wamid.S1', 'failed', [['code' => 131053, 'title' => 'Media upload error', 'error_data' => ['details' => 'Unsupported video codec']]]);
        $this->assertSame('failed', $log->fresh()->status);
        $this->assertSame('Unsupported video codec', $log->fresh()->meta['error']);
    }

    public function test_unsafe_file_types_are_served_as_downloads()
    {
        $this->actingAsAdmin();
        Storage::disk('local')->put('whatsapp/page.html', '<script>alert(1)</script>');

        $log = WhatsAppLog::create([
            'phone' => self::PHONE, 'message' => '', 'status' => 'received', 'direction' => 'inbound',
            'message_type' => 'document',
            'meta' => ['media' => ['path' => 'whatsapp/page.html', 'mime' => 'text/html', 'filename' => 'page.html']],
        ]);

        $response = $this->get(route('admin.whatsapp-logs.media', $log))->assertOk();
        $this->assertStringStartsWith('attachment', $response->headers->get('Content-Disposition'));
    }

    private function webhook(array $message): void
    {
        app(WhatsappWebhookService::class)->handleIncomingMessage([
            'entry' => [['changes' => [['value' => ['messages' => [$message]]]]]],
        ]);
    }

    private function deliveryStatus(string $wamid, string $status, array $errors = []): void
    {
        app(WhatsappWebhookService::class)->handleIncomingMessage([
            'entry' => [['changes' => [['value' => ['statuses' => [array_filter(['id' => $wamid, 'status' => $status, 'errors' => $errors])]]]]]],
        ]);
    }
}
