<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use App\Http\Requests\CreateFolder;
use App\Http\Requests\EditFolder;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    /**
     *  【フォルダ作成ページの表示機能】
     *
     *  GET /folders/create
     *  @return \Illuminate\View\View
     */
    public function showCreateForm()
    {
        $folders = Auth::user()->folders;

        return view('folders.create', compact('folders'));
    }

        /**
     *  【フォルダの作成機能】
     *
     *  POST /folders/create
     *  @param Request $request （リクエストクラスの$request）
     *  @return \Illuminate\Http\RedirectResponse
     */
    public function create(CreateFolder $request)
    {
        $folder = new Folder();
        $folder->title = $request->title;

         /** @var App\Models\User **/
        $user = Auth::user();
        $user->folders()->save($folder);

        return redirect()->route('tasks.index', ['id' => $folder->id]);
    }

    /**
     *  【フォルダ編集ページの表示機能】
     *
     *  GET /folders/{id}/edit
     *  @param int $id
     *  @return \Illuminate\View\View
     */
    public function showEditForm($id)
    {
        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);

        return view('folders.edit', [
            'folder_id' => $folder->id,
            'folder_title' => $folder->title,
        ]);
    }

    /**
     * 【フォルダの編集機能】
     *  POST /folders/{id}/edit
     *  @param int $id
     *  @param CreateFolder $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit(int $id, EditFolder $request) {
        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);

        $folder->title = $request->title;
        $folder->save();

        return redirect()->route('tasks.index', [
            'id' => $folder->id
        ]);
    }

    /**
     *  【フォルダ削除ページの表示機能】
     *
     *  GET /folders/{id}/delete
     *  @param int $id
     *  @return \Illuminate\View\View
     */
    public function showDeleteForm(int $id)
    {
        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);

        return view('folders.delete', [
            'folder_id' => $folder->id,
            'folder_title' => $folder->title,
        ]);
    }

    /**
     *  【フォルダの削除機能】
     *  機能：フォルダが削除されたらDBから削除し、フォルダ一覧にリダイレクトする
     *
     *  POST /folders/{id}/delete
     *  @param int $id
     *  @return RedirectResponse
     */
    public function delete(int $id)
    {
        /** @var App\Models\User **/
        $user = Auth::user();
        $folder = $user->folders()->findOrFail($id);

        $folder->tasks()->delete();
        $folder->delete();

        $folder = Folder::first();

        return redirect()->route('tasks.index', [
            'id' => $folder->id
        ]);
    }
}
