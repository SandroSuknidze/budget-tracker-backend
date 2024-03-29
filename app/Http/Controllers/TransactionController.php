<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::where('user_id', auth()->id())
            ->where('account_id', $request->input('accountId'))
            ->with('categories', 'account')
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json($transactions);
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'title' => 'string|max:255|nullable',
            'amount' => 'required|numeric|min:0',
            'categories' => ['required','array',
                function ($attribute, $value, $fail) use ($request) {
                    $categories = Category::whereIn('id', $value)->pluck('type')->unique();
                    if ($categories->count() > 1) {
                        $fail('Categories must belong to the same type (income or expenses).');
                    } elseif ($categories->first() !== $request->input('type')) {
                        $fail('Selected categories must match the transaction type.');
                    }
                }],
            'categories.*' => ['exists:categories,id'],
            'payment_date' => 'required|string',
            'payee' => 'nullable|string|max:255',
            'type' => ['required', 'string', 'max:255', Rule::in(['income', 'expenses'])],
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $timestamp = strtotime($request->input('payment_date'));

        $newDateFormat = date("Y-m-d H:i:s", $timestamp);

        $transaction = new Transaction();
        $transaction->user_id = auth()->id();
        $transaction->account_id = $request->input('account_id');
        $transaction->title = $request->input('title');
        $transaction->amount = $request->input('amount');
        $transaction->payment_date = $newDateFormat;
        $transaction->payee = $request->input('payee');
        $transaction->type = $request->input('type');
        $transaction->description = $request->input('description');

        $transaction->save();

        $transaction->categories()->attach($request->input('categories'));

        return response()->json(['message' => 'Transaction created successfully'], 201);
    }

    public function show($id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:transactions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = Transaction::with('account')
            ->with('categories')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction);
    }

    public function destroy($id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:transactions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = Transaction::find($id);

        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $transaction->categories()->detach();
            $transaction->delete();
            DB::commit();
            return response()->json(['message' => 'Transaction deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete transaction'], 500);
        }
    }
}
