<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;

class TaskController extends Controller
{
    public function index(int $id)
    {
        $folder = Folder::all();

        return view(
            'tasks.index',
            [
                'folders' => $folder,
                'folder_id' => $id,
            ]
        );
    }
}
