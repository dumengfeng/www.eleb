<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    //允许赋值和修改的字段
    protected $fillable = ['username','tel','password'];
    //获取logo的真实地址
    public function img(){
        return Storage::url($this->img);
    }
}
