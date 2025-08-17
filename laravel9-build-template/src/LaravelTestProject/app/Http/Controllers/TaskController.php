<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\Task;
use App\Http\Requests\CreateTask;

class TaskController extends Controller
{
    /**
     *  【タスク一覧ページの表示機能】
     *
     *  GET /folders/{id}/tasks
     *  @param int $id
     *  @return \Illuminate\View\View
     */

    public function index(int $id)
    {
        $folders = Folder::all();

        $folder = Folder::find($id);

        $tasks = $folder->tasks()->get();

        return view(
            'tasks.index',
            [
                'folders' => $folders,
                'folder_id' => $id,
                'tasks' => $tasks,
            ]
        );
    }

    /**
     *  【タスク作成ページの表示機能】
     *
     *  GET /folders/{id}/tasks/create
     *  @param int $id
     *  @return \Illuminate\View\View
     */

    public function showCreateForm(int $id)
    {
        return view(
            'tasks.create',
            [
                'folder_id' => $id,
            ]
        );
    }

    /**
     *  【タスクの作成機能】
     *
     *  POST /folders/{id}/tasks/create
     *  @param int $id
     *  @param CreateTask $request
     *  @return \Illuminate\Http\RedirectResponse
     */

    public function create(int $id, CreateTask $request)
    {
        $folder = Folder::find($id);

        $task = new Task();
        $task->title = $request->title;
        $task->due_date = $request->due_date;
        $folder->tasks()->save($task);

        return redirect()->route('tasks.index', [
            'id' => $folder->id
        ]);
    }
}
