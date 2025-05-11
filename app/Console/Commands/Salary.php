<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Models\Salary as ModelsSalary;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Salary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:salary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command tinh luong, se duoc server chay dinh ki';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //tinh luong cua nhan vien theo thang
        $timeNow = Carbon::now();
        $editors = User::with(['files' => function ($fileQuery) use ($timeNow) {
            return $fileQuery->whereMonth('created_at', $timeNow)
                ->where('status', FILE::STATUS_DONE);
        }, 'salary' => function ($salaryQuery) use ($timeNow) {
            return $salaryQuery->whereMonth('month', $timeNow);
        }])->get();
        $arrSalary = [];
        foreach ($editors as $editor) {
            $tmpSalary = count ($editor->files) * ModelsSalary::BASE_SALARY;
            //check salary for month
            if (count ($editor->salary) > 0) {
                //update current salary
                DB::table('salary')->where('id', $editor->salary[0]->id)
                    ->update(['salary' => $tmpSalary,
                            'updated_at' => $timeNow
                ]);
            } else {
                //insert to DB
                $arrSalary[] = [
                    'user_id' => $editor->id,
                    'status' => ModelsSalary::STATUS_UN_PAID,
                    'salary' => $tmpSalary,
                    'month' => $timeNow,
                    'created_at' => $timeNow,
                    'updated_at' => $timeNow
                ];
            }
        }
        DB::table('salary')->insert($arrSalary);
        echo "Update salary success!";
        return true;
    }
}
