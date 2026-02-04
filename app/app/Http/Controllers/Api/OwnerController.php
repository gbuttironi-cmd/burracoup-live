<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OwnerController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'type' => 'required|in:associazione,persona',
            'contact_email' => 'nullable|email|max:190',
            'timezone' => 'nullable|string|max:64',
            'default_refresh_seconds' => 'nullable|integer|min:30|max:600',
            'branding' => 'nullable|array',
        ]);

        $plainKey = 'ow_' . Str::random(48);

        $owner = Owner::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'slug' => Str::slug($data['name']),
            'contact_email' => $data['contact_email'] ?? null,
            'timezone' => $data['timezone'] ?? 'Europe/Rome',
            'default_refresh_seconds' => $data['default_refresh_seconds'] ?? 120,
            'branding_json' => $data['branding'] ?? null,
            'owner_key_hash' => Hash::make($plainKey),
            'is_active' => true,
        ]);

        return response()->json([
            'id' => $owner->id,
            'name' => $owner->name,
            'owner_key' => $plainKey, // MOSTRATA UNA SOLA VOLTA
            'message' => 'Salva questa chiave: servirà per creare eventi',
        ], 201);
    }
}