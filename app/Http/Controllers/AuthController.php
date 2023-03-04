<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
 use App\Http\Controllers\Controller;
 
 use Validator;
 use App\Models\User;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api',['except'=>['login','register','profile','show','update','delete']]);
    }

    public function register(Request $request){
        
        $validator = Validator::make($request->all(),
        [
            'name'=>'required',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string|confirmed|min:6'
        ]);
        
        if($validator->fails()){
            return response()->json(
                $validator->errors()->toJson(),400);
        }
        
        $user = User::create(array_merge(
            $validator->validated(),
            ['password'=>bcrypt($request->password)]
           
        ));
        
        return response()->json([
            'message'=>'User successfully registered',
            'user'=>$user
        ],201);
    }
    public function login(Request $request){
        $validator = Validator::make($request->all(),
        [
            
            'email'=>'required|string|email',
            'password'=>'required|string|min:6'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        if(!$token=auth()->attempt($validator->validated())){
            return response()->json(['error'=>'Unauth'],401);
        }
        return $this->createNewToken($token);
    }
    public function createNewToken($token){
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->factory()->getTTL()*60,
            'user'=>auth()->user()
        ]);
    }

    public function profile(){
       return response()->json(auth()->user());
    }
    public function logout(){
        auth()->logout();
        return response()->json([
            'message'=>'User logout successfully'
           
        ]);
     }
     public function show($id)
    {
        $users = user::find($id);

        if($users){
        return response()->json([
            'status'=>200,
            'users'=>$users
        ]);
    }
    else{
        return response()->json([
            'status'=>404,
            'users'=>'Id Not Found'
        ],404);
    }

    }

    public function update(Request $request, $id)
    {
        $users = user::find($id);
        if($users)
        {
            $users->name = $request->name;
            $users->email = $request->email;
            $users->password = $request->password;
            $users->update();

            return response()->json([
                'status'=>200,
                'message'=>'users Updated successfully'
            ],200);
        }
        else{
            return response()->json([
                'status'=>404,
                'users'=>'Id Not Found'
            ],404);
        }
    }
     public function delete($id)
    {
        $users = user::find($id);
        if ($users) {
            $users->delete();
            return response()->json([
                'status'=>200,
                'message'=>'User Deleted successfully'
            ],200);
        }
        else{
            return response()->json([
                'status'=>404,
                'user'=>'Id Not Found'
            ],404);
        }
    }
    
}
