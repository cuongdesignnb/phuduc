<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaAdminContractTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_media_index_is_inertia_and_picker_is_bounded_json(): void
    {
        $admin = $this->admin();
        $this->assertAdminPage($this->actingAs($admin)->get(route('admin.media.index')), 'admin_media_index');
        $this->actingAs($admin)->getJson(route('admin.media.data'))->assertOk()->assertJsonStructure(['data']);
    }
}
