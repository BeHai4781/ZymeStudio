<?php

namespace App\Http\Controllers;

use App\Mail\MailRegistered;
use App\Models\File;
use App\Models\User;
use App\Validations\UserValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
class UserController extends Controller
{
    const FILE_EXTENSIONS = ['png', 'jpg', 'jpeg'];
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $param = $request -> all();
        $users = User::where('role', User::ROLE_EDITOR)
            ->orderBy('id', 'DESC')
            ->paginate(config('const.paginate'));
        foreach ($users as $user) {
            $fileCompleted = 0;
            foreach ($user->files as $file) {
                if ($file->status == File::STATUS_DONE) {
                    $fileCompleted++;
                }
            }
            $user->file_completed = $fileCompleted;
        }
        return view('admin.users.list', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $param = $request->all();

        //Validate
        UserValidate::validate($request);
       
        $user = new User();
        $user->name = $param['name'];
        $user->email = $param['email'];
        $user->password = Hash::make($param['password']);
        $user->role == User::ROLE_EDITOR;
        $user->save();
        try {
            // Send email
            $mailRegistered = new MailRegistered();
            $mailRegistered->setEmail($param['email']);
            $mailRegistered->setPassword($param['password']);
            Mail::to($param['email'])->send($mailRegistered);
            return redirect('users');
        } catch (\Exception $e) {
            dd($e);
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return [
                'code' => 204,
                'message' => 'Data not found',
                'status' => 'success'
            ];
        }
        $files = File::where('user_id', $user->id)
            ->orderBy('priority', 'DESC')
            ->paginate(config('const.paginate'));
        if(count($files)>0){
            foreach($files as $file){
                $file->txt_priority = FILE::CONVERT_PRIORITY_TXT[
                    $file->priority
                ];
                $file->txt_status = FILE::CONVERT_STATUS_TXT[
                    $file->status
                ];
                $file->txt_synchronize = FILE::CONVERT_SYNC_TXT[
                    $file->synchronize
                ];
            }
        }
        return view('admin.users.show', compact('user', 'files'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return [
                'code' => 204,
                'message' => 'Data not found',
                'status' => 'success'
            ];
        }
        $param = $request->all();
        $user->email = $param['email'];
        $user->name = $param['name'];
        $user->password = Hash::make($param['password']);
        $user->update();
        return redirect('/users/show/' . $id);
    }

    public function singleUpload(Request $request, $id) {
        $user = User::find($id);
        $param = $request->all();
       // dd($param);
        if(!$request->hasFile('file')){
           
            return [
                'code' => 422,
                'message' => 'File not found',
                'status' => 'success'
            ];
            
        }
    
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        if(!in_array($ext, self::FILE_EXTENSIONS) ){
            return [
                'code' => 422,
                'message' => 'File not found',
                'status' => 'success'
            ];
        }

        $fileName = $file->getClientOriginalName();
        $removeExt = str_replace(
            '.png', '',
            str_replace('.jpg', '',
            str_replace('.jpeg', '',$fileName)
            )
        );
        $fileName = $removeExt . '_' . date('ymdhis'). '.' . $file->getClientOriginalExtension();
        $username = explode('@', $user->email)[0];
        $path = 'uploads/' . $username . '/';
        if (!is_dir(public_path('uploads'))) {
            mkdir(public_path('uploads'));
        }
        if (!is_dir(public_path($path))) {
            mkdir(public_path($path));
        }

        move_uploaded_file($file, $path . $fileName);
        $file = new File();
        $file->filename =$fileName;
        $file->dateline = $param['dateline'];
        $file->status = File::STATUS_ASSIGN;
        $file->priority = $param['priority'];
        $file->user_id = $user->id;
        $file->synchronize = FILE::UN_SYNC;
        $file->save();
        return redirect('/users/show/' . $user->id);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function confirm(Request $request, $id)
    {
        $file = File::find($id);
        if (!$file) {
            return [
                'code' => 204,
                'data' => null,
                'message' => "Data not found"
            ];
        }
        $file->status = File::STATUS_DONE;
        $file->update();
        return redirect()->back();
    }

    public function multipleUpload(Request $request, $userId)
    {
        $dateNow = Carbon::now();
        return view('admin.users.multiple_upload', compact(
            'userId', 'dateNow'
        ));
    }

    public function executeUpload(Request $request)
    {
        $param = $request->all();
        $user = User::find($param['user_id']);
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        $fileName = $file->getClientOriginalName();
        $removeExt = str_replace(
            '.png', '',
            str_replace('.jpg', '',
            str_replace('.jpeg', '',$fileName)
            )
        );
        $fileName = $removeExt . '_' . date('ymdhis'). '.' . $file->getClientOriginalExtension();
        $username = explode('@', $user->email)[0];
        $path = 'uploads/' . $username . '/';
        if (!is_dir(public_path('uploads'))) {
            mkdir(public_path('uploads'));
        }
        if (!is_dir(public_path($path))) {
            mkdir(public_path($path));
        }
        move_uploaded_file($file, $path . $fileName);
        $file = new File();
        $file->filename = $fileName;
        $file->dateline = $param['deadline'];
        $file->status = File::STATUS_ASSIGN;
        $file->priority = $param['priority'];
        $file->user_id = $user->id;
        $file->synchronize = FILE::UN_SYNC;
        $file->save();
        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'Upload success'
        ];
    }
}
