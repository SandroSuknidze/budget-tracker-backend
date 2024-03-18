<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
                    return $query->where('user_id', auth()->id())
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

    public function delete($id): JsonResponse|Response
    {
        $category = Category::where('user_id', auth()->id())->where('id', $id)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->noContent();
    }
}
