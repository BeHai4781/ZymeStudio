<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    const STATUS_ASSIGN = 1;
    const STATUS_CONFIRM = 2;
    const STATUS_DONE = 3;
    const PRIORITY_LOW = 1;
    const PRIORITY_MED = 2;
    const PRIORITY_HIGH = 3;
    const SYNC = 1;
    const UN_SYNC = 0;
    const CONVERT_STATUS_TXT = [
        1 => '処理',
        2 => '確認する',
        3 => '終わり'
    ];

    const CONVERT_PRIORITY_TXT = [
        1 => 'LOW',
        2 => 'MEDIUM',
        3 => 'HIGH'
    ];

    const CONVERT_SYNC_TXT = [
        0 => 'NOT SYNCHRONIZE',
        1 => 'SYNCHRONIZE'
    ];
    // connect to db
    protected $table = "files";

    //anh xa cac truong trong bang dl
    protected $filltable = [
        "filename",
        "deadline",
        "status", //1: assign, 2:confirm, 3:done
        "priority", //1: low, 2:med, 3:high
        "user_id",
        "synchronize", //dong bo hoa 1: synchronize, 0: not synchronize
        "created_at",
        "updated_at"
    ];

    /**
     * lien ket 1-1
     * 
     * su dung belongTo hoac hasOne
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
