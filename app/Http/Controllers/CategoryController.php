<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::where('user_id', auth()->id())
            ->orWhere('is_default', true)
            ->select('id', 'title', 'type', 'is_default')
            ->orderBy('title')
            ->get();

        return response()->json($categories);
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => [
                'required',
                'max:128',
                'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where(function ($query) {
                        $query->where('user_id', auth()->id())
                            ->orWhere('user_id', null);
                    })
                        ->where('type', $request->input('type'));
                }),
            ],
        ]);

        $validator->setCustomMessages([
            'title.unique' => 'A category with this name already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = new Category();
        $category->title = $request->input('title');
        $category->type = $request->input('type');
        $category->user_id = auth()->user()->id;
        $category->save();

        return response()->json([
            'message' => 'Category created successfully',
        ], 201);
    }

    public function update(Request $request, $categoryId): JsonResponse
    {
        $category = Category::where('id', $categoryId)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($category->user_id === null || $category->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized to update this category'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => [
             'required',
             'max:128',
             'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('categories')->where(function ($query) use ($categoryId) {
                    return $query->where(function ($query) use ($categoryId) {
                        $query->where('user_id', auth()->id())
                            ->orWhere('user_id', null);
                    })
                        ->where('id', '!=', $categoryId);
                }),
                Rule::unique('categories')->ignore($categoryId),
            ],
        ]);

        $validator->setCustomMessages([
            'title.unique' => 'A category with this name already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }



        $category->title = $request->input('title');
        $category->save();

        return response()->json(['message' => 'Category updated successfully']);
    }

    public function delete($id): JsonResponse|Response
    {
        $category = Category::where('user_id', auth()->id())->where('id', $id)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $hasTransactions = $category->transactions()->exists();

        if ($hasTransactions) {
            return response()->json(['message' => 'This category can\'t be removed because it contains transactions'], 422);
        }

        $category->delete();

        return response()->noContent();
    }

    public function transactionsCategoryCheck($categoryId)
    {
        $userCategory = Category::where('user_id', auth()->id())->find($categoryId);

        if (!$userCategory) {
            return false;
        }

        return $userCategory->transactions()->exists();
    }

    public function categoryUsageCheck($categoryId)
    {
        $userCategory = Category::where('id', $categoryId)->first();

        if (!$userCategory) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return Transaction::where('user_id', auth()->id())
            ->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })->exists();

    }

}
