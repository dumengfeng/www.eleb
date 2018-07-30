<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Member;
use App\Models\MenuCategory;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\Shops;
use App\SignatureHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class JsonsController extends Controller
{
    //  // 获得商家列表接口
    public function businessList()
    {
        $businessList = Shops::select('id', 'shop_name', 'shop_img', 'shop_rating', 'brand', 'on_time', 'fengniao', 'bao', 'piao', 'zhun', 'start_send', 'send_cost', 'notice', 'discount')->get();
        foreach ($businessList as $val) {
            $val['distance'] = rand(2, 20);
            $val['estimate_time'] = 10;
        }
        return json_encode($businessList);
    }

    // 获得指定商家接口
    public function business(Request $request)
    {
        $business = Shops::where('id', $request->id)->select('id', 'shop_name', 'shop_img', 'shop_rating', 'brand', 'on_time', 'fengniao', 'bao', 'piao', 'zhun', 'start_send', 'send_cost', 'notice', 'discount')->first();
        $business['distance'] = rand(10, 1000);
        $business['estimate_time'] = rand(1, 5);
        $business['service_code'] = rand(2, 20);
        $business['foods_code'] = rand(1, 5);// 食物总评分
        $business['high_or_low'] = true;// 低于还是高于周边商家
        $business['h_l_percent'] = rand(1, 5);// 低于还是高于周边商家的百分比
        $business['evaluate'] = [[
            "user_id" => 12344,
            "username" => "w******k",
            "user_img" => "http://www.homework.com/images/slider-pic4.jpeg",
            "time" => "2017-2-22",
            "evaluate_code" => 1,
            "send_time" => 30,
            "evaluate_details" => "不怎么好吃"
        ]];
        $MC = [];
        $r = MenuCategory::where('shop_id', $request->id)->get();
        foreach ($r as $v) {
            $M = [];
            $menu = Menu::where([['shop_id', $request->id], ['category_id', $v->id]])->get();
            foreach ($menu as $m) {
                $M[] = ["goods_id" => $m['id'],
                    "goods_name" => $m['goods_name'],
                    "rating" => $m['rating'],
                    "goods_price" => $m['goods_price'],
                    "description" => $m['description'],
                    "month_sales" => $m['month_sales'],
                    "rating_count" => $m['rating_count'],
                    "tips" => $m['tips'],
                    "satisfy_count" => $m['satisfy_count'],
                    "satisfy_rate" => $m['satisfy_rate'],
                    "goods_img" => $m['goods_img'],
                ];
            }
            $MC[] = [
                "description" => $v['description'],
                "is_selected" => $v['is_selected'],
                "name" => $v['name'],
                "type_accumulation" => $v['type_accumulation'],
                "goods_list" => $M
            ];
        }
        $business['commodity'] = $MC;
//dd($business);
        return json_encode($business);
    }
    // 获取短信验证码接口
    public function sms(Request $request)
    {
        $params = array();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIxSPKgYTIQqOg";
        $accessKeySecret = "4hL67Yhhqi9C0wbSKW5vnZbzcOieOz";

        // fixme 必填: 短信接收号码
        $tel = $request->tel;
//        dd($request->tel);
        $params["PhoneNumbers"] = $tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "杜孟烽";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_140595018";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $num = random_int(1000, 9999);
        $val = Redis::set('sms' . $tel, $num);
        Redis::expire('sms' . $tel, 900);
        $params['TemplateParam'] = Array(
            "code" => $num,
            //"product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );
//        return $content;
        return ([
            "status" => "ture",
            "message" => "验证码正确",
        ]);
//        dd($content);
    }
    // 注册接口
    public function regist(Request $request)
    {
        $tel = $request->tel;
        if ($request->sms != Redis::get('sms' . $tel)) {
            return [
                "status" => "flase",
                "message" => "验证码错误",
            ];
        }
        $validator = Validator::make($request->all(), [
            'username' => 'max:10|unique:members',
            'tel' => 'unique:members',
            'password' => 'min:6',
        ], [
            'username.unique' => '会员名称不能相同',
            'username.max' => '会员名称不能大于10位',
            'tel.unique' => '电话号码不能相同',
            'password.min' => '密码不能小于6位',
        ]);
        if ($validator->fails()) {
            return ([
                "status" => "false",
                "message" => $validator->errors()->first(),
            ]);
        }
        $model = Member::create([
            'username' => $request->username,
            'tel' => $request->tel,
            'password' => bcrypt($request->password),
        ]);
        return [
            "status" => "ture",
            "message" => "注册成功",
        ];
    }
    // 登录验证接口
    public function loginCheck(Request $request)
    {
        if (Auth::attempt([
            'username' => $request->name,
            'password' => $request->password
        ])
        ) {
            return [
                "status" => "true",
                "message" => "登录成功",
                "user_id" => Auth::user()->id,
                "username" => auth()->user()->username,
            ];
        } else {
            return [
                "status" => "false",
                "message" => "登录失败",
                "user_id" => "错误",
                "username" => "错误",
            ];
        }
    }

    //新增地址
    public function addAddress(Request $request)
    {
        /*
         *[{
      "status": "true",
      "message": "添加成功"
    }]
         */
        Address::create([
            "user_id" => Auth::user()->id,
            "province" => $request->provence,
            "city" => $request->city,
            "county" => $request->area,
            "address" => $request->detail_address,
            "tel" => $request->tel,
            "name" => $request->name,
            "is_default" => 0,
        ]);
        return [
            "status", "true",
            "message", "添加成功"
        ];
    }

    // 地址列表
    public function addressList()
    {
        /*
         * [{
      "id": "1",
      "provence": "四川省",
      "city": "成都市",
      "area": "武侯区",
      "detail_address": "四川省成都市武侯区天府大道56号",
      "name": "张三",
      "tel": "18584675789"
    }, ]*/
        $list = Address::select()->where('user_id', Auth::user()->id)->get();
//        dd($list);
        $A = [];
        foreach ($list as $val) {
            $A[] = [
                'id' => $val->id,
                'provence' => $val->province,
                'city' => $val->city,
                'area' => $val->county,
                'detail_address' => $val->address,
                'name' => $val->name,
                'tel' => $val->tel,
            ];
        }
//        dd($A);
        return $A;
    }

    //指定地址接口
    public function address(Request $request)
    {
        /* "id": "2",
     "provence": "河北省",
     "city": "保定市",
     "area": "武侯区",
     "detail_address": "四川省成都市武侯区天府大道56号",
     "name": "张三",
     "tel": "18584675789"*/
        $list = Address::select()->where([['user_id', Auth::user()->id], ['id', $request->id]])->first();
        $A = [
            'id' => $list->id,
            'provence' => $list->province,
            'city' => $list->city,
            'area' => $list->county,
            'detail_address' => $list->address,
            'name' => $list->name,
            'tel' => $list->tel,
        ];
        return $A;

    }

    //地址修改接口
    public function editAddress(Request $request)
    {
//        验证数据，手动验证
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'tel' => 'required',
            'provence' => 'required',
            'city' => 'required',
            'area' => 'required',
            'detail_address' => 'required',
        ], [
            'name.required' => '收货人不能为空',
            'tel.required' => '联系方式不能为空',
            'provence.required' => '省不能为空',
            'city.required' => '市不能为空',
            'area.required' => '区不能为空',
            'detail_address.required' => '详细地址不能为空',
        ]);
        if ($validator->fails()) {
            return [
                "status" => "false",
                "message" => $validator->errors()->first(),
            ];
        }
//        dd($validator);
        $address = Address::find($request->id);

        //保存修改数据
        $rel = $address->update([
            'name' => $request->name,
            'tel' => $request->tel,
            'province' => $request->provence,
            'city' => $request->city,
            'county' => $request->area,
            'address' => $request->detail_address,
            "is_default" => 0,
        ]);
//        dd($rel);
        return [
            "status" => "true",
            "message" => "修改成功"
        ];

    }

    //保存购物车接口
    public function addCart(Request $request)
    {
        //        验证数据，手动验证
        $validator = Validator::make($request->all(), [
            'goodsList' => 'required',
            'goodsCount' => 'required',
        ], [
            'goodsList.required' => '商品列表不能为空',
            'goodsCount.required' => '商品数量不能为空',
        ]);
        if ($validator->fails()) {
            return [
                "status" => "false",
                "message" => $validator->errors()->first(),
            ];
        }
        $goodsList = $request->goodsList;
        $goodsCount = $request->goodsCount;
//dd(count($goodsList));
        $cart = Cart::where('user_id', Auth::user()->id)->delete();
        for ($i = 0; $i < count($goodsList); $i++) {
            Cart::create([
                "user_id" => Auth::user()->id,
                "goods_id" => $goodsList[$i],
                "amount" => $goodsCount[$i],
            ]);
        }
        return [
            "status" => "true",
            "message" => "添加成功",
        ];
    }

    //购物车列表接口
    public function cart()
    {
        $list = Cart::select()->where('user_id', Auth::user()->id)->get();
        $A = [];
        $totals = '';
        foreach ($list as $val) {
            foreach (Menu::where('id', $val->goods_id)->get() as $v) {
                $A[] = [
                    'goods_name' => $v->goods_name,
                    'goods_id' => $val->goods_id,
                    'goods_img' => $v->goods_img,
                    'amount' => $val->amount,
                    'goods_price' => $v->goods_price,
                ];
                $totalCost = ($val->amount) * ($v->goods_price);
            }
//            $A[]=$val;
            $totals += $totalCost;
        }
        $B['goods_list'] = $A;
        $B['totalCost'] = $totals;
//        dd($A);
        return $B;
    }

    // 添加订单接口addorder
    public function addorder(Request $request)
    {
        $goods_id = Cart::where('user_id', Auth::user()->id)->first()->goods_id;
        $list = Cart::select()->where('user_id', Auth::user()->id)->get();
        $A = [];
        $totals = '';
        $totalCost = '';
        foreach ($list as $val) {
            foreach (Menu::where('id', $val->goods_id)->get() as $v) {
                $A[] = [
                    'amount' => $val->amount,
                    'goods_price' => $v->goods_price,
                ];
                $totalCost = ($val->amount) * ($v->goods_price);
            }
            $totals += $totalCost;
        }
        DB::beginTransaction();
        $O = Order::create([
            "user_id" => Auth::user()->id,
            "shop_id" => Menu::select("shop_id")->where('id', $goods_id)->first()->shop_id,
            "sn" => uniqid(),
            "province" => Address::select("province")->where('user_id', Auth::user()->id)->first()->province,
            "city" => Address::select("city")->where('user_id', Auth::user()->id)->first()->city,
            "county" => Address::select("county")->where('user_id', Auth::user()->id)->first()->county,
            "address" => Address::select("address")->where('user_id', Auth::user()->id)->first()->address,
            "tel" => Address::select("tel")->where('user_id', Auth::user()->id)->first()->tel,
            "name" => Address::select("name")->where('user_id', Auth::user()->id)->first()->name,
            'total' => $totals,//价格
            "status" => 0,
            "out_trade_no" => uniqid(),
        ]);
        foreach ($list as $val) {
            foreach (Menu::where('id', $val->goods_id)->get() as $v) {
                $OG = OrderGoods::create([
                    "order_id" => $O->id,
                    "goods_id" => $v->id,
                    "amount" => $val->amount,
                    "goods_name" => $v->goods_name,
                    "goods_img" => $v->goods_img,
                    "goods_price" => $v->goods_price,
                ]);
            }
        }
        if ($O && $OG) {
            DB::commit();
            return [
                "status" => "true",
                "message" => "添加成功",
                "order_id" => $request->order_id,
            ];
        } else {
            DB::rollBack();
            return [
                "status" => "false",
                "message" => "添加失败",
                "order_id" => $request->order_id,
            ];
        }

    }

    //获得订单列表接口
    public function orderList()
    {
        /*/**
 * "order_code": 订单号
 * "order_birth_time": 订单创建日期
 * "order_status": 订单状态
 * "shop_id": 商家id
 * "shop_name": 商家名字
 * "shop_img": 商家图片
 * "goods_list": [{//购买商品列表
 * "goods_id": "1"//
 * "goods_name": "汉堡"
 * "goods_img": "http://www.homework.com/images/slider-pic2.jpeg"
 * "amount": 6
 * "goods_price": 10
 * }]
 */
        $list = Order::select()->where('user_id', Auth::user()->id)->get();
        $A = [];
        $B = [];
        $shop_id = '';
        $status = '';
        switch ($status) {
            case $status = 0;
                $status = '待支付';
                break;
            case $status = 1;
                $status = '待发货';
                break;
            case $status = -1;
                $status = '已取消';
                break;
            case $status = 2;
                $status = '待确认';
                break;
            case $status = 3;
                $status = '完成';
                break;

        }
        $totals = '';
        $totalCost = '';
        foreach ($list as $val) {
            foreach (OrderGoods::select()->where('order_id', $val->id)->get() as $v) {
                $shop_id = Menu::select("shop_id")->where('id', $v->goods_id)->first()->shop_id;
                $B[] = [
                    'goods_id' => $v->id,
                    'goods_name' => $v->goods_name,
                    'goods_img' => $v->goods_img,
                    'amount' => $v->amount,
                    'goods_price' => $v->goods_price,
                ];
                $totalCost = ($v->amount) * ($v->goods_price);
            }
            $totals += $totalCost;
            $A[] = [
                'id'=>$val->id,
                'order_code' => uniqid().$val->id,
                'order_birth_time' => $val->create_at,
                'order_status' => $status,//状态
                'shop_id' => $shop_id,
                'shop_name' => Shops::select("shop_name")->where('id', $shop_id)->first()->shop_name,
                'shop_img' => Shops::select("shop_img")->where('id', $shop_id)->first()->shop_img,
                "goods_list" => $B,
                "order_price" => $totals,
                "order_address" => Address::select("address")->where('user_id', Auth::user()->id)->first()->address . "距离市中心约" . rand(100, 10000) . "米"//"北京市朝阳区霄云路50号 距离市中心约7378米北京市朝阳区霄云路50号 距离市中心约7378米"
            ];
        }
//        dd($A);
        return $A;
    }

    // 获得指定订单接口order
    public function order(Request $request)
    {
        /*{
        "id": "1",
        "order_code": "0000001",
        "order_birth_time": "2017-02-17 18:36",
        "order_status": "代付款",
        "shop_id": "1",
        "shop_name": "上沙麦当劳",
        "shop_img": "http://www.homework.com/images/shop-logo.png",
        "goods_list": [{
            "goods_id": "1",
            "goods_name": "汉堡",
            "goods_img": "http://www.homework.com/images/slider-pic2.jpeg",
            "amount": 6,
            "goods_price": 10
        }, {
            "goods_id": "1",
            "goods_name": "汉堡",
            "goods_img": "http://www.homework.com/images/slider-pic2.jpeg",
            "amount": 6,
            "goods_price": 10
        }],
        "order_price": 120,
        "order_address": "北京市朝阳区霄云路50号 距离市中心约7378米北京市朝阳区霄云路50号 距离市中心约7378米"
    }*/
        $list = Order::select()->where([['user_id', 1],['id',$request->id]])->first();
        $A = [];
        $B = [];
        $shop_id = '';
        $status = '';
        switch ($status) {
            case $status = 0;
                $status = '待支付';
                break;
            case $status = 1;
                $status = '待发货';
                break;
            case $status = -1;
                $status = '已取消';
                break;
            case $status = 2;
                $status = '待确认';
                break;
            case $status = 3;
                $status = '完成';
                break;

        }
        $totals = '';
        $totalCost = '';
            foreach (OrderGoods::select()->where('order_id', $list->id)->get() as $v) {
                $shop_id = Menu::select("shop_id")->where('id', $v->goods_id)->first()->shop_id;
                $B[] = [
                    'goods_id' => $v->id,
                    'goods_name' => $v->goods_name,
                    'goods_img' => $v->goods_img,
                    'amount' => $v->amount,
                    'goods_price' => $v->goods_price,
                ];
                $totalCost = ($v->amount) * ($v->goods_price);
            }
            $totals += $totalCost;
            $A = [
                'order_code' => $list->id,
                'order_birth_time' => (string)$list->created_at,
                'order_status' => $status,//状态
                'shop_id' => $shop_id,
                'shop_name' => Shops::select("shop_name")->where('id', $shop_id)->first()->shop_name,
                'shop_img' => Shops::select("shop_img")->where('id', $shop_id)->first()->shop_img,
                "goods_list" => $B,
                "order_price" => $totals,
                "order_address" => Address::select("address")->where('user_id', 1)->first()->address . "距离市中心约" . rand(100, 10000) . "米"//"北京市朝阳区霄云路50号 距离市中心约7378米北京市朝阳区霄云路50号 距离市中心约7378米"
            ];
            return $A;
        }
    // 修改密码接口changePassword
    public function changePassword(Request $request)
    {
        //        验证数据，手动验证
        $validator = Validator::make($request->all(), [
            'oldPassword' => 'required | min:6',//旧密码
            'newPassword' => 'required | min:6',//新密码

        ], [
            'name.required' => '旧密码不能为空',
            'oldPassword.min'=>'旧密码不能小于6位',
            'newPassword.min'=>'新密码不能小于6位',
            'newPassword.required' => '新密码不能为空',
        ]);
        if ($validator->fails()) {
            return [
                "status" => "false",
                "message" => $validator->errors()->first(),
            ];
        }
        if(Auth::attempt([
            'username'=>Auth::user()->username,
            'password'=>$request->oldPassword
        ])){
            Member::where('id',Auth::user()->id)->update([
                "password"=>bcrypt($request->newPassword),
            ]);
            return [
                "status" => "true",
                "message" => "修改成功"
            ];
        }else{
            return [
                "status" => "false",
                "message" => "密码错误"
            ];
        }

    }
    // 忘记密码接口forgetPassword
    public function forgetPassword(Request $request)
    {
        $tel = $request->tel;
        if ($request->sms != Redis::get('sms' . $tel)) {
            return [
                "status" => "flase",
                "message" => "验证码错误",
            ];
        }
        $validator = Validator::make($request->all(), [
            'password' => 'min:6',
        ], [
            'password.min' => '密码不能小于6位',
        ]);
        if ($validator->fails()) {
            return ([
                "status" => "false",
                "message" => $validator->errors()->first(),
            ]);
        }
            Member::where('id',Auth::user()->id)->update([
                "password"=>bcrypt($request->password),
            ]);
            return [
                "status" => "true",
                "message" => "修改成功"
            ];
    }

}