<?php

namespace App\Api\V1\Controllers\Team;

use App\Models\Team;

class ComputerController
{
    public function index(Team $team)
    {
        $computers = $team->remoteDirectories()
            ->select(\DB::raw('computer_id, count(*) as rd_count, max(datasets.updated_at) as updated_at'))
            ->join('datasets', 'remote_directories.id', '=', 'datasets.remote_directory_id')
            ->groupBy('computer_id')
            ->get();

        return response()->json($computers->makeVisible(['rd_count', 'updated_at']));
    }
}