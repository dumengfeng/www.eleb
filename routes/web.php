<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});
//Route::get('/users', 'UsersController@index')->name('users.index');//用户列表
//Route::get('/users/{user}', 'UsersController@show')->name('users.show');//查看单个用户信息
//Route::get('/users/create', 'UsersController@create')->name('users.create');//显示添加表单
//Route::post('/users', 'UsersController@store')->name('users.store');//接收添加表单数据
//Route::get('/users/{user}/edit', 'UsersController@edit')->name('users.edit');//修改用户表单
//Route::patch('/users/{user}', 'UsersController@update')->name('users.update');//更新用户信息
//Route::delete('/users/{user}', 'UsersController@destroy')->name('users.destroy');//删除用户
Route::get('/', function () {
    return view('index');
});
// 获得商家列表接口
Route::get('/shops','JsonsController@businessList');
// 获得指定商家接口
Route::get('/business','JsonsController@business');
// 获取短信验证码接口
Route::any('/sms','JsonsController@sms');
//注册regist
Route::post('/regist','JsonsController@regist');
//登录loginCheck
Route::post('/loginCheck','JsonsController@loginCheck');
//新增地址addAddress
Route::post('/addAddress','JsonsController@addAddress');
//地址列表接口addressList
Route::get('/addressList','JsonsController@addressList');
//指定地址接口address
Route::get('/address','JsonsController@address');
//地址修改接口editAddress
Route::post('/editAddress','JsonsController@editAddress');
// 保存购物车接口addCart
Route::post('/addCart','JsonsController@addCart');
//购物车列表cart
Route::get('/cart','JsonsController@cart');
// 添加订单接口addorder
Route::post('/addorder','JsonsController@addorder');
//获得订单列表接口
Route::get('/orderList','JsonsController@orderList');
// 获得指定订单接口order
Route::get('/order','JsonsController@order');
// 修改密码接口changePassword
Route::post('/changePassword','JsonsController@changePassword');
// 忘记密码接口forgetPassword
Route::post('/forgetPassword','JsonsController@forgetPassword');
//发邮件
Route::get('/send','JsonsController@send');
//短信提醒

//中文词典
Route::get('/search',function (\Illuminate\Support\Facades\Request $request){
    $cl = new \App\SphinxClient();
    $cl->SetServer ( '127.0.0.1', 9312);
    $cl->SetConnectTimeout ( 10 );
    $cl->SetArrayResult ( true );
    $cl->SetMatchMode ( SPH_MATCH_EXTENDED2);
    $cl->SetLimits(0, 1000);
    $info = request()->keyword;
    $res = $cl->Query($info, 'shop');//shopstore_search
    print_r($res);
});
