<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarrantyLookupController extends Controller
{
    public function index()
    {
        return Inertia::render('Guest/WarrantyLookup');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
        ]);

        $warranty = Warranty::where('serial_number', $request->serial_number)->first();

        return Inertia::render('Guest/WarrantyLookup', [
            'warranty' => $warranty,
            'searched' => true,
        ]);
    }
}
