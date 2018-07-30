<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    //允许赋值和修改的字段
    protected $fillable = ['name','type_accumulation','shop_id','description','is_selected','goods_name','rating','shop_id','category_id','goods_price','month_sales','description','rating_count','tips','satisfy_count','satisfy_rate','goods_img','status'];
}
