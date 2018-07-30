<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //允许赋值和修改的字段 * id	primary	主键
//user_id	int	用户id
//province	string	省
//city	string	市
//county	string	县
//address	string	详细地址
//tel	string	收货人电话
//name	string	收货人姓名
//is_default	int	是否是默认地址
    protected $fillable = ['user_id','province','city','county','address','tel','name','is_default','id'];
}
