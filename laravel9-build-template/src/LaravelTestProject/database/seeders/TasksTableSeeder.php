<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(1, 3) as $num) {
            DB::table(
                'tasks'
            )->insert(
                [
                    'folder_id' => 1, // Assuming folders with IDs 1 to 3 exist
                    'title' => "タスク $num",
                    'due_date' => Carbon::now()->addDays($num), // Random due date within the next 30 days
                    'status' => $num, // Random status (0, 1, or 2)
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
