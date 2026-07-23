<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\WarrantyLookupRequest;
use App\Models\Warranty;
use App\Services\Storefront\StorefrontSeoService;
use App\Services\Storefront\WarrantyPresentationService;
use Inertia\Inertia;

class WarrantyLookupController extends Controller
{
    private const FAILURE = 'Không tìm thấy thông tin bảo hành phù hợp với thông tin đã cung cấp.';

    public function index(StorefrontSeoService $seo)
    {
        return Inertia::render('Guest/WarrantyLookup', [
            'searched' => false,
            'warranty' => null,
            'message' => null,
            'seo' => $seo->meta(['title' => 'Tra cứu bảo hành', 'robots' => 'noindex, nofollow']),
        ]);
    }

    public function lookup(WarrantyLookupRequest $request, WarrantyPresentationService $warranties, StorefrontSeoService $seo)
    {
        $warranty = Warranty::query()
            ->where('serial_number', $request->string('serial_number'))
            ->whereHas('order', fn ($query) => $query->where('customer_phone', $request->string('customer_phone')))
            ->first();

        return Inertia::render('Guest/WarrantyLookup', [
            'searched' => true,
            'warranty' => $warranty ? $warranties->present($warranty) : null,
            'message' => $warranty ? null : self::FAILURE,
            'seo' => $seo->meta(['title' => 'Tra cứu bảo hành', 'robots' => 'noindex, nofollow']),
        ]);
    }
}
