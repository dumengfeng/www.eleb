<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderGoods extends Model
{
    protected $table="order_goods";
    protected $fillable=[
        "id",//	primary	主键
        "order_id",//	int	订单id
        "goods_id",//	int	商品id
        "amount",//	int	商品数量
        "goods_name",//	string	商品名称
        "goods_img",//	string	商品图片
        "goods_price",//	decimal	商品价格
    ];
}
