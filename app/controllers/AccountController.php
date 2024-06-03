<?php
class AccountController extends BaseController{

    public function getSignIn(){
      return View::make('account.signin');
    }
    public function postSignIn(){
    $validator=Validator::make(Input::all(),
    array(
    'email'=>'required|email',
    'password'=>'required'
      ));
     if($validator->fails()){
     return Redirect::route('account-sign-in')
     ->withErrors($validator)
     ->withInput();
     }else{
      //checkbox
      $remember=(Input::has('remember'))?true: false;
     $auth=Auth::attempt(array(
     'email'=>Input::get('email'),
     'password'=>Input::get('password'),
     'active'=>1
      ),$remember);
     if($auth){
      //Redirect to the intended page
      return Redirect::intended('/');
     }else{
      return Redirect::route('account-sign-in')
      ->with('global','Email/Password wrong or account  not Activated');
     }

     }
    return Redirect::route('account-sign-in')
      ->with('global','There was problem signing you in');
}
    
 public function getSignOut()
 {
  Auth::logout();
  return Redirect::route('home');
 }
  public function getCreate(){
//return'create';
  	return View::make('account.create');
    }
   public function postCreate(){
   	$validator=Validator::make(Input::all(),
   		array(
   			'email'=>'required|max:50|email|unique:users',
   			'username'=>'required|max:20|min:3|unique:users',
   			'password'=>'required|min:6',
   			'password_again'=>'required|same:password'
   			));
   	if(	$validator->fails()){
   return Redirect::route('account-create')
   ->withErrors($validator)
   ->withInput();

   	}else{
  //die('sucess');
      $email=Input::get('email');
      $username=Input::get('username');
      $password=Input::get('password');
     
     //Activation Code
     $code=str_random(60);

     $user=User::create(array(
     'email'=>$email,
     'username'=>$username,
     'password'=>Hash::make($password),
     'code'=>$code,
     'active'=> 0
      )); 
     //send email
     if($user){
      Mail::send('emails.auth.activate',array('link'=>URL::route('account-activate',$code),'username' =>$username),function($message)use($user){ 
      $message->to($user->email,$user->username)->subject('Activate Your Account');
      });
      
      return Redirect::route('home')
      ->with('global','Your account has been created-please check your mail to activate your account.');

     }  
}

   }

   public function getActivate($code){
    $user=User::where('code', '=',$code)->where('active','=',0);{
    if($user->count()){
    $user=$user->first();
    //UPdate user to active
   $user->active=1;
   $user->code ='';
   if($user->save()){
 return Redirect::route('home')->with('global','Your Account Activated - You can now Sign In');
    
   }
    }
     return Redirect::route('home')->with('global','we could not   activate your accout right now-please try again later');
    
 }
   }


    public function getChangePassword(){
      return View::make('account.password');
    }
    public function postChangePassword(){
      $validator=Validator::make(Input::all(),

      array(
     'old_password'=>'required',
     'password'   =>'required|min:6',
     'password_again'=>'required|same:password'
        )
        );
      if($validator->fails()){

      return Redirect::route('account-change-password')
      ->withErrors($validator);
      }else{
      $user=User::find(Auth::user()->id);
      $old_password=Input::get('old_password');
      $password=Input::get('password');
      if(Hash::check($old_password,$user->getAuthpassword()))
     {
       $user->password=Hash::make($password);

              if($user->save()){
          return Redirect::route('home')
      ->with('global','Your Password Sucessfully changed.');
        }
      }else{
         return Redirect::route('account-change-password')
    ->with('global','Your Old Password is incorrect');
      }

      }
    return Redirect::route('account-change-password')
    ->with('global','Your Password could not
       be changed');

    } 

} 