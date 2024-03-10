<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
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

    public function show(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $account = Account::where('user_id', auth()->id())->where('id', $request->id)->get();

        return response()->json($account);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:128|unique:accounts,title,' . $id,
            'currency' => 'required',
            'description' => 'max:256',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $account = Account::where('user_id', auth()->id())->where('id', $id)->first();

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $account->title = $request->title;
        $account->currency = $request->currency;
        $account->description = $request->description;

        $account->save();

        return response()->json(['message' => 'Account updated successfully'], 201);
    }

    public function delete($id): Response|JsonResponse
    {
        $account = Account::where('user_id', auth()->id())->where('id', $id)->first();

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $account->delete();

        return response()->noContent();
    }
}
