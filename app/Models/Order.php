<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table="orders";
    protected $fillable=[
        "id",	//primary	主键,
        "user_id",//int	用户ID
        "shop_id",	//int	商家ID
        "sn",	//string	//订单编号
        "province",	//string	省
        "city",	//string	市
        "county",	//string	县
        "address",	//string	详细地址
        "tel",	//	string	收货人电话
        "name",	//	string	收货人姓名
        "total",	//	decimal	价格
        "status",	//	int	状态(-1:已取消,0:待支付,1:待发货,2:待确认,3:完成)
        "created_at",	//	datetime	创建时间
        "out_trade_no",	//	string	第三方交易号（微信支付需要）"
    ];
}
