<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function registration() {
        return view('front.account.registration');
    }

    public function registrationProcess(Request $request) {

        $validator = Validator::make($request->all(), [
            'name'              => 'required',
            'email'             => 'required|email|unique:users, email',
            'password'          => 'required|min:5|same:confirm_password',
            'confirm_password'  => 'required'
        ]);

        if( $validator->passes() ){

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'Account register successfully!!');

            return response()->json([
                'status'        => true,
                'errors'        => []
            ]);
        }else{
            return response()->json([
                'status'        => false,
                'errors'        => $validator->errors()
            ]);
        }
    }

    public function login() {
        return view('front.account.login');
    }

    public function authenticate(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if( $validator->passes() ){
            if( Auth::attempt(['email' => $request->email, 'password' => $request->password]) ){
                return redirect()->route('account.profile');
            } else {
                return redirect()->route('account.login')->with('error', 'Email and Password does not match');
            }
        } else {
            return redirect()->route('account.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }
    }

    public function profile(){
        return view('front.account.profile');
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('account.login');
    }
}
