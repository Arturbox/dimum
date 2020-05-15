<?php

use Illuminate\Support\Facades\Route;

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
$namespacePrefix = '\\'.config('voyager.controllers.namespace').'\\';
Route::get('language/{lang}', function ($lang) {
//    dd($lang);
    session()->put('locale',$lang);
    App::getLocale($lang);
    return back();
})->name('langroute');
Route::get('e-admission', ['uses' => $namespacePrefix.'VoyagerFormController@form', 'as' => 'form']);

Route::group(['prefix' => '/'], function () {
    Voyager::routes();
});
