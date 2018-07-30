<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'id',    //primary	主键
        'user_id',    //int	用户id
        'goods_id',    //int	商品id
        'amount',    //int	商品数量
    ];
}
