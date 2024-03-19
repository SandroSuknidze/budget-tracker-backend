<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(): JsonResponse
    {
        $transactions = Transaction::where('user_id', auth()->id())
            ->with('categories', 'account')
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json($transactions);
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'title' => 'required|string|max:255',
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
            'payment_date' => 'required|date',
            'payee' => 'nullable|string|max:255',
            'type' => ['required', 'string', 'max:255', Rule::in(['income', 'expenses'])],
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = new Transaction();
        $transaction->user_id = auth()->id();
        $transaction->account_id = $request->input('account_id');
        $transaction->title = $request->input('title');
        $transaction->amount = $request->input('amount');
        $transaction->payment_date = $request->input('payment_date');
        $transaction->payee = $request->input('payee');
        $transaction->type = $request->input('type');
        $transaction->description = $request->input('description');

        $transaction->save();

        $transaction->categories()->attach($request->input('categories'));

        return response()->json(['message' => 'Transaction created successfully'], 201);
    }
}
