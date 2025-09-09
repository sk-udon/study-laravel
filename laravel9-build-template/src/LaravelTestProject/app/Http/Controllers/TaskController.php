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
            /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);

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
     *  POST /folders/{id}/tasks/create
     *  @param int $id
     *  @param CreateTask $request
     *  @return \Illuminate\Http\RedirectResponse
     */

    public function create(int $id, CreateTask $request)
    {
        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);

        $task = new Task();
        $task->title = $request->title;
        $task->due_date = $request->due_date;
        $folder->tasks()->save($task);

        return redirect()->route('tasks.index', [
            'id' => $folder->id
        ]);
    }

    /**
     *  【タスク編集ページの表示機能】
     *  機能：タスクIDをフォルダ編集ページに渡して表示する
     *
     *  GET /folders/{id}/tasks/{task_id}/edit
     *  @param int $id
     *  @param int $task_id
     *  @return \Illuminate\View\View
     */

    public function showEditForm(int $id, int $task_id){

        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);

        $task = $folder->tasks()->find($task_id);

        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    /**
     *  【タスクの編集機能】
     *  機能：タスクが編集されたらDBを更新処理をしてタスク一覧にリダイレクトする
     *
     *  POST /folders/{id}/tasks/{task_id}/edit
     *  @param int $id
     *  @param int $task_id
     *  @param EditTask $request
     *  @return \Illuminate\Http\RedirectResponse
     */

    public function edit(int $id, int $task_id, EditTask $request)
    {

        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);

        $task = $folder->tasks()->find($task_id);

        $task->title = $request->title;
        $task->status = $request->status;
        $task->due_date = $request->due_date;
        $task->save();

        return redirect()->route('tasks.index', [
            'id' => $task->folder_id,
        ]);
    }

    /**
     *  【タスク削除ページの表示機能】
     *
     *  GET /folders/{id}/tasks/{task_id}/delete
     *  @param int $id
     *  @param int $task_id
     *  @return \Illuminate\View\View
     */
    public function showDeleteForm(int $id, int $task_id)
    {

        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);
        $task = $folder->tasks()->findOrFail($task_id);

        return view('tasks.delete', [
            'task' => $task,
        ]);
    }

    /**
     *  【タスクの削除機能】
     *  機能：タスクが削除されたらDBから削除し、タスク一覧にリダイレクトする
     *
     *  POST /folders/{id}/tasks/{task_id}/delete
     *  @param int $id
     *  @param int $task_id
     *  @return RedirectResponse
     */
    public function delete(int $id, int $task_id)
    {

        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);
        $task = $folder->tasks()->findOrFail($task_id);

        $task->delete();

        return redirect()->route('tasks.index', [
            'id' => $task->folder_id
        ]);
    }
}
