<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Tag;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50|unique:tags,name',
            ], [
                'name.required' => 'タグ名を入力してください。',
                'name.string' => 'タグ名は文字列で入力してください。',
                'name.max' => 'タグ名は50文字以内で入力してください。',
                'name.unique' => 'そのタグは既に存在します。',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $tag = Tag::create([
                'name' => trim($request->name),
            ]);

            return response()->json([
                'status' => 'success',
                'tag' => $tag,
                'message' => 'タグを作成しました。',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TagController in store: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'タグの作成に失敗しました。'
            ], 500);
        }
    }

    public function destroy(Tag $tag)
    {
        try {
            $tag->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'タグを削除しました。',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TagController in destroy: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'タグの削除に失敗しました。'
            ], 500);
        }
    }
}
