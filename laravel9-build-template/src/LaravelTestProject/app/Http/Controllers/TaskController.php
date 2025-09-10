<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\Task;
use App\Http\Requests\CreateTask;
use App\Http\Requests\EditTask;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     *  【タスク一覧ページの表示機能】
     *
     *  GET /folders/{folder}/tasks
     *  @param Folder $folder
     *  @return \Illuminate\View\View
     */

    public function index(Folder $folder)
    {
        /** @var App\Models\User **/
        $user = Auth::user();
        $folders = $user->folders()->get();

        $tasks = $folder->tasks()->get();

        return view(
            'tasks.index',
            [
                'folders' => $folders,
                'folder_id' => $folder->id,
                'tasks' => $tasks,
            ]
        );
    }

    /**
     *  【タスク作成ページの表示機能】
     *
     *  GET /folders/{folder}/tasks/create
     *  @param Folder $folder
     *  @return \Illuminate\View\View
     */

    public function showCreateForm(Folder $folder)
    {
            /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($folder->id);

        return view(
            'tasks.create',
            [
                'folder_id' => $folder->id,
            ]
        );
    }

    /**
     *  【タスクの作成機能】
     *
     *  POST /folders/{folder}/tasks/create
     *  @param Folder $folder
     *  @param CreateTask $request
     *  @return \Illuminate\Http\RedirectResponse
     */

    public function create(Folder $folder, CreateTask $request)
    {
        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($folder->id);

        $task = new Task();
        $task->title = $request->title;
        $task->due_date = $request->due_date;
        $folder->tasks()->save($task);

        return redirect()->route('tasks.index', [
            'folder' => $folder->id
        ]);
    }

    /**
     *  【タスク編集ページの表示機能】
     *  機能：タスクIDをフォルダ編集ページに渡して表示する
     *
     *  GET /folders/{folder}/tasks/{task_id}/edit
     *  @param Folder $folder
     *  @param int $task_id
     *  @return \Illuminate\View\View
     */

    public function showEditForm(Folder $folder, int $task_id){

        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($folder->id);

        $task = $folder->tasks()->find($task_id);

        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    /**
     *  【タスクの編集機能】
     *  機能：タスクが編集されたらDBを更新処理をしてタスク一覧にリダイレクトする
     *
     *  POST /folders/{folder}/tasks/{task}/edit
     *  @param Folder $folder
     *  @param Task $task
     *  @param EditTask $request
     *  @return \Illuminate\Http\RedirectResponse
     */

    public function edit(Folder $folder, Task $task, EditTask $request)
    {

        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($folder->id);

        $task = $folder->tasks()->find($task->id);

        $task->title = $request->title;
        $task->status = $request->status;
        $task->due_date = $request->due_date;
        $task->save();

        return redirect()->route('tasks.index', [
            'folder' => $task->folder_id,
        ]);
    }

    /**
     *  【タスク削除ページの表示機能】
     *
     *  GET /folders/{folder}/tasks/{task}/delete
     *  @param Folder $folder
     *  @param Task $task
     *  @return \Illuminate\View\View
     */
    public function showDeleteForm(Folder $folder, Task $task)
    {

        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($folder->id);
        $task = $folder->tasks()->findOrFail($task->id);

        return view('tasks.delete', [
            'task' => $task,
        ]);
    }

    /**
     *  【タスクの削除機能】
     *  機能：タスクが削除されたらDBから削除し、タスク一覧にリダイレクトする
     *
     *  POST /folders/{folder}/tasks/{task}/delete
     *  @param Folder $folder
     *  @param Task $task
     *  @return RedirectResponse
     */
    public function delete(Folder $folder, Task $task)
    {

        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($folder->id);
        $task = $folder->tasks()->findOrFail($task->id);

        $task->delete();

        return redirect()->route('tasks.index', [
            'folder' => $folder->id
        ]);
    }
}
