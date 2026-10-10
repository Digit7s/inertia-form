<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaFormIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_inertia_create_page_has_schema_defaults_and_all_native_field_types(): void
    {
        $this->get(route('items.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Items/Form')
                ->where('formPayload.method', 'post')
                ->where('formPayload.action', route('items.store'))
                ->where('formPayload.defaults.title', null)
                ->where('formPayload.defaults.password', '')
                ->has('formPayload.schema', 10)
                ->where('formPayload.schema.3.type', 'select')
                ->where('formPayload.schema.4.type', 'checkbox-list')
                ->where('formPayload.schema.5.type', 'radio')
                ->where('formPayload.schema.6.type', 'grid')
                ->where('formPayload.schema.6.schema.0.type', 'date')
                ->where('formPayload.schema.6.schema.1.type', 'time')
                ->where('formPayload.schema.7.type', 'datetime')
                ->where('formPayload.schema.9.type', 'file'));
    }

    public function test_real_http_create_validates_persists_upload_and_redirects_to_edit(): void
    {
        Storage::fake('public');

        $response = $this->from(route('items.create'))->post(route('items.store'), $this->validPayload([
            'attachment' => UploadedFile::fake()->create('notes.txt', 12, 'text/plain'),
        ]));

        $item = Item::firstOrFail();
        $response->assertRedirect(route('items.edit', $item));
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'title' => 'First item',
            'category' => 'work',
        ]);
        $this->assertSame(['urgent'], $item->tags);
        $this->assertSame('Reference 1', $item->details['reference']);
        $this->assertNotNull($item->attachment);
        Storage::disk('public')->assertExists($item->attachment);
    }

    public function test_formrequest_errors_redirect_and_are_shared_as_inertia_errors(): void
    {
        $this->from(route('items.create'))
            ->post(route('items.store'), $this->validPayload([
                'title' => '',
                'details' => ['reference' => ''],
                'start_date' => 'not-a-date',
                'attachment' => UploadedFile::fake()->create('too-large.txt', 5000, 'text/plain'),
            ]))
            ->assertRedirect(route('items.create'))
            ->assertSessionHasErrors(['title', 'details.reference', 'start_date', 'attachment']);

        // The FormRequest redirects with Laravel's real flashed validation bag; Inertia's Vue form maps these keys to the matching controls.
        $this->assertSame('The title field is required.', session()->get('errors')->getBag('default')->first('title'));
        $this->assertSame('The details.reference field is required.', session()->get('errors')->getBag('default')->first('details.reference'));
    }

    public function test_edit_hydrates_record_identity_and_file_bearing_patch_persists(): void
    {
        Storage::fake('public');
        $oldPath = UploadedFile::fake()->create('old.txt')->store('items', 'public');
        $item = Item::create($this->validPayload(['attachment' => $oldPath]));

        $this->get(route('items.edit', $item))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Items/Form')
                ->where('recordId', $item->id)
                ->where('formPayload.method', 'put')
                ->where('formPayload.action', route('items.update', $item))
                ->where('formPayload.defaults.title', 'First item')
                ->where('formPayload.defaults', fn ($defaults) => $defaults->get('details.reference') === 'Reference 1')
                ->where('formPayload.defaults.attachment', $oldPath)
                ->where('formPayload.defaults.password', ''));

        $this->post(route('items.update', $item), $this->validPayload([
            '_method' => 'PATCH',
            'title' => 'Updated item',
            'details' => ['reference' => 'Reference 2'],
            'attachment' => UploadedFile::fake()->create('new.txt', 8, 'text/plain'),
        ]))->assertRedirect(route('items.edit', $item));

        $item->refresh();
        $this->assertSame('Updated item', $item->title);
        $this->assertSame('Reference 2', $item->details['reference']);
        $this->assertNotSame($oldPath, $item->attachment);
        Storage::disk('public')->assertExists($item->attachment);
    }

    public function test_invalid_spoofed_update_keeps_record_and_reports_errors(): void
    {
        $item = Item::create($this->validPayload());

        $this->from(route('items.edit', $item))
            ->post(route('items.update', $item), $this->validPayload([
                '_method' => 'PUT',
                'title' => '',
                'details' => ['reference' => ''],
            ]))
            ->assertRedirect(route('items.edit', $item))
            ->assertSessionHasErrors(['title', 'details.reference']);

        $this->assertSame('First item', $item->fresh()->title);
    }

    public function test_update_without_a_new_file_preserves_the_existing_upload(): void
    {
        Storage::fake('public');
        $existingPath = UploadedFile::fake()->create('existing.txt')->store('items', 'public');
        $item = Item::create($this->validPayload(['attachment' => $existingPath]));

        $this->post(route('items.update', $item), $this->validPayload([
            '_method' => 'PATCH',
            'attachment' => null,
            'title' => 'Edit without replacement upload',
        ]))->assertRedirect(route('items.edit', $item));

        $this->assertSame($existingPath, $item->fresh()->attachment);
        Storage::disk('public')->assertExists($existingPath);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'title' => 'First item',
            'details' => ['reference' => 'Reference 1'],
            'notes' => 'A plain text note',
            'category' => 'work',
            'tags' => ['urgent'],
            'priority' => 'high',
            'start_date' => '2026-10-10',
            'start_time' => '09:30',
            'appointment_at' => '2026-10-10T09:30',
            'password' => 'temporary-secret',
        ], $overrides);
    }
}
