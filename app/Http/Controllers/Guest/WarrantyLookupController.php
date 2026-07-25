<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\WarrantyLookupRequest;
use App\Services\Admin\Operations\WarrantyLookupService;
use App\Services\Storefront\StorefrontSeoService;
use App\Services\Storefront\WarrantyPresentationService;
use Inertia\Inertia;

class WarrantyLookupController extends Controller
{
    private const FAILURE = 'Không tìm thấy thông tin bảo hành phù hợp với thông tin đã cung cấp.';

    public function index(StorefrontSeoService $seo)
    {
        return Inertia::render('Guest/WarrantyLookup', ['page' => $this->page($seo, ['searched' => false, 'result' => null, 'message' => null])]);
    }

    public function lookup(WarrantyLookupRequest $request, WarrantyLookupService $lookup, WarrantyPresentationService $presentation, StorefrontSeoService $seo)
    {
        $warranty = $lookup->find($request->string('serial_number')->toString(), $request->string('customer_phone')->toString());

        return Inertia::render('Guest/WarrantyLookup', ['page' => $this->page($seo, ['searched' => true, 'result' => $warranty ? $presentation->present($warranty) : null, 'message' => $warranty ? null : self::FAILURE])]);
    }

    private function page(StorefrontSeoService $seo, array $lookup): array
    {
        return [
            'type' => 'warranty_lookup',
            'seo' => $seo->meta(['title' => 'Tra cứu bảo hành', 'robots' => 'noindex, nofollow']),
            'breadcrumbs' => [['name' => 'Trang chủ', 'url' => route('home')], ['name' => 'Tra cứu bảo hành']],
            'lookup' => $lookup,
        ];
    }
}
