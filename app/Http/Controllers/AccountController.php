<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|max:128',
            'currency' => 'required',
            'balance' => 'required|numeric|min:0',
            'description' => 'max:256',
        ]);

        Account::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'currency' => $request->currency,
            'balance' => $request->balance,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Account created successfully'], 201);
    }
}
