<?php


/*Controls the logging in and logging out of website along
 * with some redirects for certain website calls(admin)
 * also controls the editing of user information once logged in
* calling /logout will route to here and logout
* calling /login will route to here and give login page
* calling /admin will route to here and give the edit page of logged 
* in user. 
* calling /session/{username}/edit will also give admin user page
* only if {username} is the authuser
* also has function for updating user profiles if called from a form*/

class SessionsController extends \BaseController {
		
	protected $user;
	
	public function __construct(User $user){
		$this->user = $user;
	}
	
	
	//Redirects all /admin calls to an 
	//editing session for logged in user to make access easier for user
	public function admin()
	{
		$currentuser = Auth::user()->username;
		$url = '/sessions/'.$currentuser.'/edit';
		return Redirect::to($url);
	}
	
	//Brings in the editing form view, 
	//only allows access if authuser = requested user
	public function edit($id)
	{
		if(Auth::user()['username'] != $id){
			return Redirect::to('/')->with('info', 'You Dont have Access to that Page');
		}
		
		$user = Auth::user();
		return View::make('sessions.edit')->with('user', $user);
	}
	
	
	//Gives a login page to user
	//If logged in redirects to admin page. 
	public function create()
	{
		//Checking if loged in, gives admin page if true
		if(Auth::check())
		{
			$currentuser = Auth::user()->username;
			$url = '/sessions/'.$currentuser.'/edit';
			return Redirect::to($url);
		}
		
		//Give login page otherwise
		return View::make('sessions.create');
		
	}
	
	//Checks credentials inputed on /login or /sessions/create page
	public function store(){
		
		if(Auth::attempt(Input::only('username' , 'password')))
		{
			return Redirect::to('/');

		}
		return Redirect::back()->withInput()->with('badInfo', 'Either Username or Password is Wrong, Please try again.');
	}
	
	//logs out and returns to login page
	public function destroy()
	{
		Auth::logout();
		return Redirect::route('sessions.create');
	}
	
	public function update($id)
	{
		//Finds user from username to get user that is being updated
		$user = User::find( Auth::user()->id );
		$input = Input::all();
		
		//unset unneeded fields from form
		unset($input['_method']);
		unset($input['_token']);
		
		//validate inputed information
		$validation = Validator::make($input, User::$rules2);
		
		//Checks input for validation to make sure feilds are not blank
		if ( ! $validation->passes()){
			return Redirect::back()->withInput()->withErrors($validation);
		}
		
		//hashes the password to protect it
		$input['password'] = Hash::make($input['password']);
		unset($input['password_confirmation']);
		
		//Updated validated and hashed user information to User model
		$user->update($input);
		$currentuser = Auth::user()->username;
			$url = '/sessions/'.$currentuser.'/edit';
			return Redirect::to($url)->with('info', 'Succesfully Updated Info');

	}
	

	
}
