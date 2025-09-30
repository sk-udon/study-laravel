<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Folder;
use App\Models\Task;
use App\Http\Requests\CreateTask;
use App\Http\Requests\EditTask;

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
        try {
            /** @var App\Models\User **/
            $user = auth()->user();
            $folders = $user->folders()->get();
            $tasks = $folder->tasks()->get();

            return view('tasks/index', [
                'folders' => $folders,
                'folder_id' => $folder->id,
                'tasks' => $tasks
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in index: ' . $e->getMessage());
        }
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
        try {
            /** @var App\Models\User **/
            $user = Auth::user();
            $folder = $user->folders()->findOrFail($folder->id);

            return view('tasks/create', [
                'folder_id' => $folder->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in showCreateForm: ' . $e->getMessage());
        }
    }

    /**
     *  【タスクの作成機能】
     *
     *  POST /folders/{folder}/tasks/create
     *  @param Folder $folder
     *  @param CreateTask $request
     *  @return \Illuminate\Http\RedirectResponse
     *  @var App\Http\Requests\CreateTask
     */
    public function create(Folder $folder, CreateTask $request)
    {
        try {
            /** @var App\Models\User **/
            $user = Auth::user();
            $folder = $user->folders()->findOrFail($folder->id);

            $task = new Task();
            $task->title = $request->title;
            $task->due_date = $request->due_date;
            $folder->tasks()->save($task);

            return redirect()->route('tasks.index', [
                'folder' => $folder->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in create: ' . $e->getMessage());
        }
    }

    /**
     *  【タスク編集ページの表示機能】
     *
     *  GET /folders/{folder}/tasks/{task}/edit
     *  @param Folder $folder
     *  @param Task $task
     *  @return \Illuminate\View\View
     */
    public function showEditForm(Folder $folder, Task $task)
    {
        try {
            $this->checkRelation($folder, $task);

            /** @var App\Models\User **/
            $user = Auth::user();
            $folder = $user->folders()->findOrFail($folder->id);
            $task = $folder->tasks()->findOrFail($task->id);

            return view('tasks/edit', [
                'task' => $task,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in showEditForm: ' . $e->getMessage());
        }
    }

    /**
     *  【タスクの編集機能】
     *
     *  POST /folders/{folder}/tasks/{task}/edit
     *  @param Folder $folder
     *  @param Task $task
     *  @param EditTask $request
     *  @return \Illuminate\Http\RedirectResponse
     */
    public function edit(Folder $folder, Task $task, EditTask $request)
    {
        try {
            $this->checkRelation($folder, $task);

            /** @var App\Models\User **/
            $user = Auth::user();
            $folder = $user->folders()->findOrFail($folder->id);
            $task = $folder->tasks()->findOrFail($task->id);

            $task->title = $request->title;
            $task->status = $request->status;
            $task->due_date = $request->due_date;
            $task->save();

            return redirect()->route('tasks.index', [
                'folder' => $task->folder_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in edit: ' . $e->getMessage());
        }
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
        try {
            $this->checkRelation($folder, $task);

            /** @var App\Models\User **/
            $user = Auth::user();
            $folder = $user->folders()->findOrFail($folder->id);
            $task = $folder->tasks()->findOrFail($task->id);

            return view('tasks/delete', [
                'task' => $task,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in showDeleteForm: ' . $e->getMessage());
        }
    }

    /**
     *  【タスクの削除機能】
     *
     *  POST /folders/{folder}/tasks/{task}/delete
     *  @param Folder $folder
     *  @param Task $task
     *  @return \Illuminate\View\View
     */
    public function delete(Folder $folder, Task $task)
    {
        try {
            $this->checkRelation($folder, $task);

            /** @var App\Models\User **/
            $user = Auth::user();
            $folder = $user->folders()->findOrFail($folder->id);
            $task = $folder->tasks()->findOrFail($task->id);

            $task->delete();

            return redirect()->route('tasks.index', [
                'folder' => $task->folder_id
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in delete: ' . $e->getMessage());
        }
    }

    /**
     *  【フォルダーとタスクの関連性チェック機能】
     *
     *  @param Folder $folder
     *  @param Task $task
     *  @return void
     */
    private function checkRelation(Folder $folder, Task $task)
    {
        if ($folder->id !== $task->folder_id) {
            abort(404);
        }
    }
}
