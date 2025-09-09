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
                                                <a href="{{ route('tasks.index', ['id' => $folder->id]) }}" class="list-group-item {{ $folder_id === $folder->id ? 'active' : '' }}">
                                                    {{ $folder->title }}
                                                </a>
                                            </td>
                                            <td><a href="{{ route('folders.edit', ['id' => $folder->id])}}">編集</a></td>
                                            <td><a href="{{ route('folders.delete', ['id' => $folder->id]) }}">削除</a></td>
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
                                <a href="{{ route('tasks.create', ['id' => $folder_id]) }}" class="btn btn-default btn-block">
                                    タスクを追加する
                                </a>
                            </div>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>タイトル</th>
                                    <th>状態</th>
                                    <th>期限</th>
                                    <th></th>
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
                                    <td>{{ $task->formatted_due_date }}</td>
                                    <td><a href="{{ route('tasks.edit', ['id' => $folder_id, 'task_id' => $task->id]) }}">編集</a></td>
                                    <td><a href="{{ route('tasks.delete', ['id' => $folder_id, 'task_id' => $task->id]) }}">削除</a></td>
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
@endsection
