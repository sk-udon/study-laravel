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
                    <div class="panel-heading">タスクを追加する</div>
                    <div class="panel-body">
                        @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $message)
                                <p>{{ $message }}</p>
                            @endforeach
                        </div>
                        @endif
                        <form action="{{ route('tasks.create', ['folder' => $folder_id]) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="title">タイトル</label>
                                <input type="text" class="form-control" name="title" id="title" value="{{ old('title') }}" />
                            </div>
                            <div class="form-group">
                                <label for="due_date">期限</label>
                                <input type="text" class="form-control" name="due_date" id="due_date" value="{{ old('due_date') }}" />
                            </div>
                            <div class="form-group">
                                <label>タグ</label>

                                {{-- 既存タグ選択 --}}
                                <div class="tag-selection">
                                    @foreach($tags as $tag)
                                        <div class="checkbox tag-item">
                                            <label>
                                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                                    {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                                <span class="tag-name">{{ $tag->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- 新しいタグ作成 --}}
                                <div class="new-tag-section" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                                    <label for="new-tag-name">新しいタグを作成</label>
                                    <div class="input-group">
                                        <input type="text" id="new-tag-name" class="form-control"
                                               placeholder="タグ名を入力してください" maxlength="50">
                                        <span class="input-group-btn">
                                            <button type="button" id="create-tag-btn" class="btn btn-primary">
                                                作成
                                            </button>
                                        </span>
                                    </div>
                                    <div id="tag-creation-message" style="margin-top: 10px;"></div>
                                </div>
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
      const createTagBtn = document.getElementById('create-tag-btn');
      const newTagInput = document.getElementById('new-tag-name');
      const messageDiv = document.getElementById('tag-creation-message');
      const tagSelection = document.querySelector('.tag-selection');

      // デバッグ用
      console.log('CSRF Token:', window.csrfToken);

      // タグ作成ボタンのクリックイベント
      createTagBtn.addEventListener('click', function() {
          const tagName = newTagInput.value.trim();

          if (!tagName) {
              showMessage('タグ名を入力してください。', 'error');
              return;
          }

          // ボタンを無効化
          createTagBtn.disabled = true;
          createTagBtn.textContent = '作成中...';

          // Ajax リクエスト
          fetch('{{ route("tags.store") }}', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': window.csrfToken,
                  'X-Requested-With': 'XMLHttpRequest'
              },
              body: JSON.stringify({
                  name: tagName
              })
          })
          .then(response => {
              console.log('Response status:', response.status);
              console.log('Response headers:', response.headers);

              if (response.ok || response.status === 422) {
                return response.json();
              } else {
                  throw new Error(`HTTP error! status: ${response.status}`);
              }

              return response.json();
          })
          .then(data => {
              console.log('Response data:', data);

              if (data.status === 'success') {
                  // 新しいタグを選択肢に追加
                  addNewTagOption(data.tag);

                  // 入力をクリア
                  newTagInput.value = '';

                  // 成功メッセージ
                  showMessage(data.message, 'success');
              } else {
                  // エラーメッセージを表示
                  let errorMessage = '';
                  if (data.errors && data.errors.name) {
                      errorMessage = data.errors.name[0];
                  } else {
                      errorMessage = data.message || 'タグの作成に失敗しました。';
                  }

                  showMessage(errorMessage, 'error');
              }
          })
          .catch(error => {
              console.error('Fetch Error:', error);
              showMessage(error.message || 'タグの作成に失敗しました。', 'error');
          })
          .finally(() => {
              // ボタンを有効化
              createTagBtn.disabled = false;
              createTagBtn.textContent = '作成';
          });
      });

      // Enterキーでタグ作成
      newTagInput.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
              e.preventDefault();
              createTagBtn.click();
          }
      });

      // 新しいタグオプションを追加する関数
      function addNewTagOption(tag) {
          const checkbox = document.createElement('div');
          checkbox.className = 'checkbox tag-item';
          checkbox.innerHTML = `
            <label>
                <input type="checkbox" name="tags[]" value="${tag.id}" checked>
                <span class="tag-name">${tag.name}</span>
            </label>
          `;
          tagSelection.appendChild(checkbox);
      }

      // メッセージを表示する関数
      function showMessage(message, type) {
          const className = type === 'success' ? 'alert-success' : 'alert-danger';
          messageDiv.innerHTML = `<div class="alert ${className}">${message}</div>`;

          // 3秒後にメッセージを消す
          setTimeout(() => {
              messageDiv.innerHTML = '';
          }, 3000);
      }
  });
</script>

@endsection
