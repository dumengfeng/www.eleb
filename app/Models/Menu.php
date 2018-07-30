<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['goods_name','rating','shop_id','category_id','goods_price','month_sales','description','rating_count','tips','satisfy_count','satisfy_rate','goods_img','status','user_id'];
}
