<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', ['as' => 'login', 'uses' => 'LoginController@login']);
Route::post('login', 'LoginController@login');
Route::post('register', 'RegisterController@register');
Route::apiResource('users', 'UserController');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('user', 'AuthenticationController@user');
    Route::post('logout', 'LoginController@logout');

    Route::get('users/check/me', 'UserController@meUser');
});

//Admin
Route::get('adm/users', 'UserController@users');
Route::get('adm/users/{id}', 'UserController@getUserData');
Route::get('adm/users/{id}/orders', 'UserController@getUserOrders');
Route::post('adm/users/{id}/update', 'UserController@UserDataUpdate');
Route::post('adm/users/delete/checked', 'UserController@deleteChecked');


Route::apiResource('adm/categories', 'CategoryController');
Route::get('adm/categories/options/data', 'CategoryController@optionsData');
Route::post('adm/categories/delete/checked', 'CategoryController@deleteChecked');

Route::apiResource('adm/products', 'ProductController');
Route::patch('adm/products/{id}/sell', 'ProductController@sell');
Route::patch('adm/products/{id}/reset', 'ProductController@reset');
Route::patch('adm/products/{id}/set-zone', 'ProductController@setZone');
Route::delete('adm/products/attribute/delete/{id}/{attr}', 'ProductController@destroyProductAttribute');
Route::post('adm/products/gallery/save', 'ProductController@galleryStore');
Route::get('adm/products/options/data', 'ProductController@optionsData');
Route::post('adm/products/delete/checked', 'ProductController@deleteChecked');
Route::post('adm/products/c/copy', 'ProductController@copy');

Route::post('adm/orders/{id}/create', 'OrderController@sell');

Route::post('adm/orders/{id}/edit', 'OrderController@edit');

Route::apiResource('adm/orders', 'OrderController');
Route::get('adm/orders-list', 'OrderController@ordersList');
Route::get('adm/orders-list/{id}', 'OrderController@ordersListData');
Route::get('adm/item-orders', 'OrderController@itemOrders');
Route::post('adm/orders-orders/delete/checked', 'OrderController@deleteCheckedList');
Route::post('adm/orders-orders-id/delete/checked', 'OrderController@deleteCheckedList');
Route::get('adm/orders/options/data', 'OrderController@optionsData');
Route::post('adm/orders/delete/checked', 'OrderController@deleteChecked');
Route::get('adm/orders/{order_id}/set-status', 'OrderController@setStatus');

Route::patch('adm/orders/{id}/set-zone', 'OrderController@setZone');

Route::apiResource('adm/media', 'MediaController');
Route::post('adm/media/sort', 'MediaController@sort');
Route::delete('adm/media/delete-gallery/{id}', 'MediaController@deleteGallery');
Route::post('adm/upload/media', 'MediaController@uploadProductMedia');
Route::post('adm/files/delete', 'MediaController@deleteFile');

//Web
Route::apiResource('categories', 'Web\CategoryController');
Route::apiResource('products', 'Web\ProductController');
Route::get('new/products', 'Web\ProductController@newAll');
Route::get('last/products', 'Web\ProductController@newLimit');
Route::get('related/products', 'Web\ProductController@related');
Route::get('archive', 'Web\ProductController@archiveAll');

//Check type for multi-nested-slug page
Route::get('type/{url}', 'Web\ProductController@type')->where('url', '(.*)');
Route::get('search/products', 'Web\ProductController@search');

Route::post('order', 'Web\OrderController@order');



Route::get('adm/areas/index', 'AreaController@index');
Route::get('adm/areas/{id}/delete', 'AreaController@delete');
Route::post('adm/areas/create', 'AreaController@create');

Route::get('adm/statistic/get', 'AreaController@statistic');
Route::post('adm/statistic/set', 'AreaController@setStatistic');



Route::post('adm/finances/articles/add', 'FinanceController@addArticle');
Route::post('adm/finances/expenses/add', 'FinanceController@addExpense');
Route::get('adm/finances/articles/get', 'FinanceController@getArticles');
Route::get('adm/finances/expenses/get', 'FinanceController@getExpenses');
Route::get('adm/finances/articles/{id}/delete', 'FinanceController@deleteArticle');
Route::get('adm/finances/expenses/{id}/delete', 'FinanceController@deleteExpense');

Route::get('adm/finances/orders/get', 'FinanceController@getOrders');
Route::get('adm/statistic/get-orders', 'OrderController@count');