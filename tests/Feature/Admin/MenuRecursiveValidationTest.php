<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuRecursiveValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_nested_validation_reports_the_exact_child_path(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);

        $this->actingAs($this->admin())->postJson(route('admin.menus.items', $menu), [
            'items' => [[
                'client_key' => 'root', 'title' => 'Gốc', 'model_type' => 'url', 'url' => '/',
                'children' => [['client_key' => 'child', 'model_type' => 'url', 'url' => '/', 'children' => []]],
            ]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.children.0.title');
    }

    public function test_menu_rejects_depth_over_four_and_more_than_one_hundred_nodes(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $deep = ['client_key' => 'one', 'title' => 'Một', 'model_type' => 'url', 'url' => '/', 'children' => []];
        for ($i = 2; $i <= 5; $i++) {
            $deep = ['client_key' => "node-{$i}", 'title' => "Mục {$i}", 'model_type' => 'url', 'url' => '/', 'children' => [$deep]];
        }
        $tooMany = array_map(fn (int $i) => ['client_key' => "item-{$i}", 'title' => "Mục {$i}", 'model_type' => 'url', 'url' => '/', 'children' => []], range(1, 101));

        $this->actingAs($this->admin())->postJson(route('admin.menus.items', $menu), ['items' => [$deep]])
            ->assertUnprocessable()->assertJsonValidationErrors('items.0.children.0.children.0.children.0.children');
        $this->actingAs($this->admin())->postJson(route('admin.menus.items', $menu), ['items' => $tooMany])
            ->assertUnprocessable()->assertJsonValidationErrors('items');
    }

    public function test_menu_requires_strict_ids_and_unique_client_keys(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $base = ['client_key' => 'same', 'title' => 'Mục', 'model_type' => 'url', 'url' => '/', 'children' => []];

        $this->actingAs($this->admin())->postJson(route('admin.menus.items', $menu), ['items' => [
            [...$base, 'id' => '1'], [...$base, 'id' => 2],
        ]])->assertUnprocessable()->assertJsonValidationErrors(['items.0.id', 'items.1.client_key']);
    }

    private function admin(): \App\Models\User
    {
        return \App\Models\User::factory()->admin()->create();
    }
}
