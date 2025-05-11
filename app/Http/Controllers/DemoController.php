<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\File;
use App\Models\Salary;
use Illuminate\Support\Facades\DB;

class DemoController extends Controller
{
    public function index(Request $request)
    {
        // 2 cach viet lenh sql trong laravel
        // query builder: thao tac truc tiep toi bang csdl bang lenh sql
        $data = DB::table('users')
            ->select(
                "id",
                "email",
                "name",
                "created_at",
                "role"
            )
            ->where('role', User::ROLE_ADMIN)
            ->get();
        if (!empty($data)) {
            foreach ($data as $user) {
                $user->role = "admin";
            }
        }
        return $data;
    }
    public function listJob(Request $request, $editorId)
    {

        $files = DB::table('files')

            ->select(
                "users.name",
                "files.filename",
                "files.deadline",
                "files.status",
                "files.priority",
                "files.synchronize",
                "files.id"
            )
            ->join('users', 'users.id', '=', 'files.user_id')
            ->where('users.id', $editorId)
            ->where('users.role', User::ROLE_EDITOR)
            ->orderBy('files.id', 'desc')
            ->paginate(config('const.paginate'))
            ->map(function ($item) {
                $item->status = File::CONVERT_STATUS_TXT[$item->status];
                $item->priority = File::CONVERT_PRIORITY_TXT[$item->priority];
                $item->synchronize = File::CONVERT_SYNC_TXT[$item->synchronize];
                return $item;
            });

        return $files;
    }
    public function sortUserFile(Request $request)
    {
        $param = $request->all();
        $users = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw("COUNT(files.id) as total_file")
            )
            ->join("files", "users.id", "files.user_id")
            ->where('users.role', User::ROLE_EDITOR)
            ->groupBy('users.id')
            ->groupBy('users.name')
            ->groupBy('users.email')
            ->orderBy('total_file', isset($param['sort']) ? $param['sort'] : 'DESC')
            ->get();
        return $users;
    }
    public function eloquent(Request $request)
    {
        $param = $request->all();
        /**
         * eloquent se render ra lenh SQL thong qua Model
         *
         * De su dung relation ship se goi thong qua with
         */
        $users = User::with('files')
            ->where(function ($query) use ($param) {
                if (isset($param['editor_name']) && isset($param['file_name'])) {
                    $query->where('name', 'like', '%' . $param['editor_name'] . '%');
                }
                return $query;
            }) ->whereHas('files', function($queryFile) use ($param) {
                if (isset($param['file_name']) && isset($param['editor_name'])) {
                    $queryFile->where('filename', 'like', '%' . $param['file_name'] . '%');
                }
                return $queryFile;
            })
            ->where('role', User::ROLE_EDITOR)
            ->get()->map(function ($item) {
                foreach ($item->files as $file) {
                    $file->status = File::CONVERT_STATUS_TXT[$file->status];
                    $file->priority = File::CONVERT_PRIORITY_TXT[$file->priority];
                    $file->synchronize = File::CONVERT_SYNC_TXT[$file->synchronize];
                }
                return $item;
            });

        //truyen du lieu ve view su dung compact
        return view('demo_eloquent', compact('users'));
    }
}