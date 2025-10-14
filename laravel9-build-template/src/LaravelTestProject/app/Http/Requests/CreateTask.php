<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTask extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|max:100',
            'due_date' => 'required|date|after_or_equal:today',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }

    /**
     * リクエストのnameなどの値を再定義するメソッド
     *
     * @return array<string>
     */
    public function attributes()
    {
        return [
            'title' => 'タイトル',
            'due_date' => '期限日',
            'tags' => 'タグ',
        ];
    }

    /**
     * FormRequestクラス単位でエラーメッセージを定義するメソッド
     *
     * @return array<string>
     */
    public function messages()
    {
        return [
            'due_date.after_or_equal' => ':attribute は今日以降の日付を指定してください。',
        ];
    }
}
