<?php

namespace Tests\Feature\Admin;

use App\Services\Admin\AdminPresentationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminPaginationContractTest extends TestCase
{
    public function test_pagination_contract_has_plain_text_labels_and_current_page(): void
    {
        $paginator = new LengthAwarePaginator(range(16, 30), 70, 15, 2, ['path' => '/admin/products']);
        $pagination = app(AdminPresentationService::class)->pagination($paginator);

        $this->assertSame(2, $pagination['current_page']);
        $this->assertSame(5, $pagination['last_page']);
        $this->assertSame('previous', $pagination['links'][0]['key']);
        $this->assertFalse($pagination['links'][0]['disabled']);
        $this->assertSame('page-2', $pagination['links'][2]['key']);
        $this->assertTrue($pagination['links'][2]['active']);
        $this->assertStringNotContainsString('<', implode(' ', array_column($pagination['links'], 'label')));

        $firstPage = app(AdminPresentationService::class)->pagination(new LengthAwarePaginator(range(1, 15), 70, 15, 1, ['path' => '/admin/products']));
        $this->assertTrue($firstPage['links'][0]['disabled']);
        $this->assertNull($firstPage['links'][0]['url']);
    }
}
