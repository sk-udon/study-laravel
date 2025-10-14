<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Folder;
use App\Models\Task;
use App\Models\Tag;
use App\Http\Requests\CreateTask;
use App\Http\Requests\EditTask;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     *  【タスク一覧ページの表示機能】
     *
     *  GET /folders/{folder}/tasks
     *  @param Folder $folder
     *  @return \Illuminate\View\View
     */
    public function index(Folder $folder, Request $request)
    {
        try {
            /** @var App\Models\User **/
            $user = auth()->user();
            $folders = $user->folders()->get();
            $folder = $user->folders()->findOrFail($folder->id);

            $selectedTagIds = $request->get('tags');
            $selectedTagIds = $selectedTagIds ? [$selectedTagIds] : [];

            $tasksQuery = $folder->tasks()->with('tags');

            if (!empty($selectedTagIds)) {
                $tasksQuery->whereHas('tags', function($query) use($selectedTagIds){
                    $query->whereIn('tags.id', $selectedTagIds);
                });
            }

            $tasks = $tasksQuery->get();

            $allTags = Tag::orderBy('name')->get();

            return view('tasks/index', [
                'folders' => $folders,
                'folder_id' => $folder->id,
                'tasks' => $tasks,
                'tags' => $allTags,
                'selectedTagIds' => $selectedTagIds,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in index: ' . $e->getMessage());
            return redirect()->back()
                ->with(['error' => 'タスク一覧の表示に失敗しました。']);
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

            $tags = Tag::orderBy('name')->get();

            return view('tasks/create', [
                'folder_id' => $folder->id,
                'tags' => $tags,
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

            DB::transaction(function() use ($request, $folder) {
                $task = new Task();
                $task->title = $request->title;
                $task->due_date = $request->due_date;
                $folder->tasks()->save($task);

                // タグの関連付け
                if ($request->has('tags')) {
                    $tagIds = $request->input('tags');
                    $task->tags()->sync($tagIds);
                }
            });

            return redirect()->route('tasks.index', [
                'folder' => $folder->id,
            ])->with('success', 'タスクを作成しました。');

        } catch (\Throwable $e) {
            Log::error('Error TaskController in create: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with(['error' => 'タスクの作成に失敗しました。']);
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
            $task = $folder->tasks()->with('tags')->findOrFail($task->id);

            $tags = Tag::select('tags.*')
                ->leftJoin('tag_task', function($join) use ($task) {
                    $join->on('tags.id', '=', 'tag_task.tag_id')
                         ->where('tag_task.task_id', '=', $task->id);
                })
                ->orderByRaw('CASE WHEN tag_task.task_id IS NOT NULL THEN 0 ELSE 1 END')
                ->orderBy('tags.name')
                ->get();

            return view('tasks/edit', [
                'task' => $task,
                'tags' => $tags,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error TaskController in showEditForm: ' . $e->getMessage());
            return redirect()->back()
                ->with(['error' => 'タスクの編集ページの表示に失敗しました。']);
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

            DB::transaction(function() use ($request, $task) {
                $task->title = $request->title;
                $task->status = $request->status;
                $task->due_date = $request->due_date;
                $task->save();

                // タグの関連付け
                if ($request->has('tags') && is_array($request->tags)) {
                    $task->tags()->sync($request->tags);
                } else {
                    // タグが選択されていない場合は全て削除
                    $task->tags()->detach();
                }
            });

            return redirect()->route('tasks.index', [
                'folder' => $task->folder_id,
            ])->with('success', 'タスクを更新しました。');

        } catch (\Throwable $e) {
            Log::error('Error TaskController in edit: ' . $e->getMessage());
            return redirect()->back()
                ->with(['error' => 'タスクの更新に失敗しました。']);
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
