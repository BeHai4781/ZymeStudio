<?php

namespace App\Http\Controllers;

use App\Models\File;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EditorController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->all();
        $userId = Auth::user()->id;

        // Query files belonging to the current user
        $files = File::where('user_id', $userId)
            ->where(function ($query) use ($param) {
                if (isset($param['month']) && !is_null($param['month'])) {
                    $monthNow = Carbon::create($param['month']);
                    return $query->whereMonth('created_at', $monthNow);
                } else {
                    // Get current month
                    $monthNow = new Carbon();
                    return $query->whereMonth('created_at', $monthNow);
                }
            })
            ->get()
            ->map(function ($item) {
                $item->txt_priority = File::CONVERT_PRIORITY_TXT[$item->priority];
                return $item;
            });
        // Return HTML view, passing the fetched and processed files
        return view('editors.index', compact('files'));
    }

    public function update(Request $request, $id)
    {
        $param = $request->all();
        $file = File::find($id);
        if (isset($param['file']) && !is_null($param['file'])) {
            // Use strrpos instead of strpos
            $lastDot = strrpos($file->filename, '.');
            $name = substr($file->filename, 0, $lastDot);
            // Extract the file extension
            $extension = substr($file->filename, $lastDot + 1);
            // Generate a new filename with a timestamp to avoid duplicates
            $fileName = $name . "_done." . $extension;
            //move file to folder
            $folder = explode('@', Auth::user()->email)[0];
            $path = public_path('uploads/' . $folder . '/' . $fileName);
            $fileUpload = $request->file('file');
            move_uploaded_file($fileUpload, $path);
            return redirect()->back();
        }
        
        $file->status = isset($param['status']) ? File::STATUS_CONFIRM : File::STATUS_ASSIGN;
        $file->save();
        return redirect()->back();
    }

    public function download(Request $request, $id) 
    {
        $folder = explode('@', Auth::user()->email)[0];
        $file = File::find($id);
        $path = "";
        if (!$file) {
            return "404 not found";
        }
        $path = public_path('uploads/' . $folder . '/' . $file->filename);
        if (!$file || !file_exists($path)) {
            return "404 not found";
        }
        return response()->download($path);
    } 
}