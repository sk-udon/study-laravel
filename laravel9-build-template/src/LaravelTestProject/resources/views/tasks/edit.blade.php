@extends('layout')

@section('styles')
  @include('share.flatpickr.styles')
  <style>
    /* Bootstrap上書き防止のため特異性を高める */
    .form-group .tag-selection {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
        align-items: flex-start !important;
        margin-bottom: 15px !important;
        max-height: 200px !important;
        overflow-y: auto !important;
    }

    /* Bootstrap checkboxクラスを上書き */
    .form-group .tag-selection .tag-item.checkbox {
        display: inline-flex !important;
        align-items: center !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        background-color: #f9f9f9 !important;
        margin: 0 !important;
        margin-bottom: 0 !important; /* Bootstrap checkboxのmargin-bottomを無効化 */
        padding-left: 0 !important; /* Bootstrap checkboxのpadding-leftを無効化 */
        padding: 6px 10px !important;
        position: relative !important;
    }

    .form-group .tag-selection .tag-item.checkbox label {
        display: flex !important;
        align-items: center !important;
        margin: 0 !important;
        margin-bottom: 0 !important; /* Bootstrap labelのmargin-bottomを無効化 */
        padding-left: 0 !important; /* Bootstrap checkboxのpadding-leftを無効化 */
        cursor: pointer !important;
        white-space: nowrap !important;
        font-weight: normal !important; /* Bootstrap labelのfont-weightを無効化 */
    }

    .form-group .tag-selection .tag-item input[type="checkbox"] {
        margin: 0 6px 0 0 !important;
        position: static !important; /* Bootstrap checkboxのpositionを無効化 */
        margin-left: 0 !important; /* Bootstrap checkboxのmargin-leftを無効化 */
    }

    .form-group .tag-selection .tag-item .tag-name {
        white-space: nowrap !important;
    }

    .new-tag-section {
        margin-top: 15px !important;
        padding-top: 15px !important;
        border-top: 1px solid #ddd !important;
    }

    .new-tag-section .input-group {
        margin-top: 10px !important;
    }

    #tag-creation-message {
        margin-top: 10px !important;
    }

    .delete-tag-btn {
        margin-left: 8px !important;
        padding: 2px 6px !important;
        font-size: 12px !important;
    }

    /* タグが多い場合のスクロール対応（オプション） */
    .tag-selection {
        max-height: 200px;
        overflow-y: auto;
    }
  </style>
@endsection

@section('content')
<div class="container">
        <div class="row">
            <div class="col col-md-offset-3 col-md-6">
                <nav class="panel panel-default">
                    <div class="panel-heading">タスクを編集する</div>
                    <div class="panel-body">
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <form action="{{ route('tasks.edit', ['folder' => $task->folder_id, 'task' => $task->id]) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="title">タイトル</label>
                                <input type="text" class="form-control" name="title" id="title"
                                    value="{{ old('title') ?? $task->title }}"
                                />
                            </div>
                            <div class="form-group">
                                <label for="status">状態</label>
                                <select name="status" id="status" class="form-control">
                                    @foreach(\App\Models\Task::STATUS as $key => $val)
                                        <option value="{{ $key }}" {{ $key == old('status', $task->status) ? 'selected' : '' }}>
                                            {{ $val['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tags">タグ</label>
                                <div class="tag-selection">
                                    @foreach($tags as $tag)
                                        <div class="checkbox tag-item" data-tag-id="{{ $tag->id }}">
                                            <label>
                                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                                    {{ $task->tags->contains($tag) ? 'checked' : '' }}>
                                                <span class="tag-name">{{ $tag->name }}</span>
                                            </label>
                                            <button type="button" class="delete-tag-btn btn btn-sm"
                                                    data-tag-id="{{ $tag->id }}" data-tag-name="{{ $tag->name }}">
                                                削除
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="due_date">期限</label>
                                <input type="text" class="form-control" name="due_date" id="due_date" value="{{ old('due_date') ?? $task->formatted_due_date }}" />
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">送信</button>
                            </div>
                        </form>
                    </div>
                </nav>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
  @include('share.flatpickr.scripts')

 <script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteTagButtons = document.querySelectorAll('.delete-tag-btn');
        deleteTagButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const tagId = e.target.dataset.tagId;
                const tagName = e.target.dataset.tagName;

                if (!confirm(`タグ「${tagName}」を削除します。よろしいですか？\n※このタグを使用している他のタスクからも削除されます。`)) {
                    return;
                }

                const button = e.target;
                const originalText = button.textContent;
                button.disabled = true;
                button.textContent = '削除中...';

                fetch(`/tags/${tagId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response Error:' + response.status);
                    }

                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);

                    if (data.status === 'success') {
                        // タグ要素を削除
                        const tagItem = button.closest('.tag-item');
                        console.log('Removing tag item:', tagItem);
                        if (tagItem) {
                            tagItem.remove();
                        }
                        alert(`タグ「${tagName}」を削除しました。`);
                    } else {
                        alert(data.message || 'タグの削除に失敗しました。');
                    }
                                    button.disabled = false;
                button.textContent = originalText;
                })
            });
        });
    });


 </script>
@endsection
