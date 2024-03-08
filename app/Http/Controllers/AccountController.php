<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = auth()->id();

        $accounts = Account::where('user_id', $userId)->get();

        return response()->json($accounts);
    }
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:128|unique:accounts,title',
            'currency' => 'required',
            'description' => 'max:256',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        Account::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'currency' => $request->currency,
            'balance' => 0,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Account created successfully'], 201);
    }
}
