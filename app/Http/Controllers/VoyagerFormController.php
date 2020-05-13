<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\VoyagerBaseController as BaseVoyagerBaseController;

class VoyagerFormController extends BaseVoyagerBaseController
{
    use RegistersUsers;
    use \Illuminate\Auth\MustVerifyEmail;

    public function form(Request $request){
        $dataType = Voyager::model('DataType')->where('slug', '=', 'applications')->first();
        $this->removeRelationshipField($dataType, 'add');
        $dataTypeRows = $dataType->addRows;
        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? new $dataType->model_name()
            : false;
        $dataFilters = Voyager::model('DataFilter')->where('data_type_parent_id', $dataType->id )->where('parent_id','=',null)->orderBy('order')->get();
        return Voyager::view('vendor/voyager/form', compact('dataTypeRows','dataType','dataTypeContent','dataFilters'));
    }

    public function store(Request $request)
    {
//        dd($request);
        $data = $request->all();
        $password = Str::random(12);
        $user = User::create([
            'name' => $data['name_app'],
            'email' => $data['mail_app'],
            'password' => Hash::make($password),
            'role_id' => 3
        ]);

//        Auth::login($user);
//        $user->sendEmailVerificationNotification();
        return parent::store($request);
    }
}
