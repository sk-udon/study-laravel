<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\Task;

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
}
