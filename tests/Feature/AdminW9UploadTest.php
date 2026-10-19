<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminW9UploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $trainer;

    protected function setUp(): void
    {
        parent::setUp();

        $club = Club::create(['name' => 'Test Club', 'slug' => 'test-club', 'subscription_status' => 'active']);
        app()->instance('currentClub', $club);
        config(['app.club_slug' => 'test-club']);

        $this->admin = User::factory()->create(['role' => 'admin', 'club_id' => $club->id]);
        $this->trainer = User::factory()->create(['club_id' => $club->id]);

        Storage::fake();
    }

    public function test_admin_can_upload_a_w9_for_a_trainer(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.trainers.w9.upload', $this->trainer), [
                'w9' => UploadedFile::fake()->create('w9.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->trainer->refresh();
        $this->assertSame("w9s/{$this->trainer->id}_w9.pdf", $this->trainer->w9_path);
        $this->assertNotNull($this->trainer->w9_uploaded_at);
        $this->assertNotNull($this->trainer->w9_received_at);
        Storage::assertExists($this->trainer->w9_path);
    }

    public function test_non_pdf_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.trainers.w9.upload', $this->trainer), [
                'w9' => UploadedFile::fake()->image('w9.png'),
            ])
            ->assertSessionHas('error');

        $this->assertNull($this->trainer->fresh()->w9_path);
    }

    public function test_trainer_cannot_upload_for_another_trainer(): void
    {
        $other = User::factory()->create(['club_id' => $this->trainer->club_id]);

        $this->actingAs($other)
            ->post(route('admin.trainers.w9.upload', $this->trainer), [
                'w9' => UploadedFile::fake()->create('w9.pdf', 100, 'application/pdf'),
            ])
            ->assertForbidden();
    }
}
