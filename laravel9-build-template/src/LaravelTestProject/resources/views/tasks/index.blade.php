@extends('layout')

@section('styles')
    @include('share.flatpickr.styles')
@endsection

@section('content')
        <div class="container">
            <div class="row">
                <div class="col col-md-4">
                    <nav class="panel panel-default">
                        <div class="panel-heading">フォルダ</div>
                        <div class="panel-body">
                            <a href="{{ route('folders.create') }}" class="btn btn-default btn-block">
                                フォルダを追加する
                            </a>
                        </div>
                        <div class="list-group">
                            <table class="table foler-table">
                                @foreach($folders as $folder)
                                    @if($folder->user_id === Auth::user()->id)
                                        <tr>
                                            <td>
                                                <a href="{{ route('tasks.index', ['folder' => $folder->id]) }}" class="list-group-item {{ $folder_id === $folder->id ? 'active' : '' }}">
                                                    {{ $folder->title }}
                                                </a>
                                            </td>
                                            <td><a href="{{ route('folders.edit', ['folder' => $folder->id])}}">編集</a></td>
                                            <td><a href="{{ route('folders.delete', ['folder' => $folder->id]) }}">削除</a></td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        </div>
                    </nav>
                </div>
                <div class="column col-md-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">タスク</div>
                        <div class="panel-body">
                            <div class="text-right">
                                <a href="{{ route('tasks.create', ['folder' => $folder_id]) }}" class="btn btn-default btn-block">
                                    タスクを追加する
                                </a>
                            </div>
                        </div>
                        <div class="tag-filter-section" style="padding: 20px; border-bottom: 1px solid #ddd;">
                            <form method="GET" action="{{ route('tasks.index', ['folder' => $folder_id]) }}" id="tag-filter-form">
                                <h5>タグで絞り込み</h5>
                                <div class="tag-filter-list">
                                    <select name="tags" class="form-control"
                                            onchange="document.getElementById('tag-filter-form').submit();">
                                        <option value="">-- すべてのタスク --</option>
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}"
                                                    {{ !empty($selectedTagIds) && $selectedTagIds[0] == $tag->id ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-controls" style="margin: 20px 0;">
                                    <button type="button" id="clear-filter-btn" class="btn btn-sm btn-default">
                                        フィルターをクリア
                                    </button>
                                    <span class="filter-status">
                                        @if(empty($selectedTagIds))
                                            全てのタスクを表示中
                                        @endif
                                    </span>
                                </div>
                            </form>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>タイトル</th>
                                    <th>状態</th>
                                    <th>タグ</th>
                                    <th>期限</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>
                                        <span class="label {{ $task->status_class }}">{{ $task->status_label }}</span>
                                    </td>
                                    <td>
                                        @if($task->tags->isNotEmpty())
                                            <div class="task-tags">
                                                @foreach($task->tags as $tag)
                                                    <span class="task-tag">{{ $tag->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $task->formatted_due_date }}</td>
                                    <td><a href="{{ route('tasks.edit', ['folder' => $task->folder_id, 'task' => $task->id]) }}">編集</a></td>
                                    <td><a href="{{ route('tasks.delete', ['folder' => $task->folder_id, 'task' => $task->id]) }}">削除</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('scripts')
  @include('share.flatpickr.scripts')

  <script>
      document.getElementById('clear-filter-btn').addEventListener('click', function() {
          const select = document.querySelector('select[name="tags"]');
          select.value = '';
          document.getElementById('tag-filter-form').submit();
      });
    </script>
@endsection
