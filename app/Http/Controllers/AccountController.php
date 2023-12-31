<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use function Laravel\Prompts\error;

class AccountController extends Controller
{
    public function registration() {
        return view('front.account.registration');
    }

    public function registrationProcess(Request $request) {

        $validator = Validator::make($request->all(), [
            'name'              => 'required',
            'email'             => 'required|email|unique:users',
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
        $id = Auth::user()->id;
        $user = User::where('id', $id)->first();
        return view('front.account.profile', ['user' => $user]);
    }

    public function profileUpdate(Request $request) {
        $id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'name'  => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,'. $id .',id'
        ]);

        if( $validator->passes() ){
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->designation = $request->designation;
            $user->mobile = $request->mobile;
            $user->save();

            session()->flash('success', 'Profile Updated Successfully!!');

            return response()->json([
                'status'    => true,
                'errors'    => []
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'errors'    => $validator->errors()
            ]);
        }
    }

    public function updateProfilePic(Request $request){
        $id = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image'
        ]);

        if( $validator->passes() ){
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = $id . '-' . time() . '.' . $ext;
            $image->move(public_path('/profile_pic/'), $imageName);

            // Create small thumbnail
            $sourcePath = public_path('/profile_pic/' . $imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);
            $image->cover(150, 150);
            $image->toPng()->save(public_path('/profile_pic/thumb/' . $imageName));

            File::delete(public_path('/profile_pic/thumb/' . Auth::user()->image));
            File::delete(public_path('/profile_pic/' . Auth::user()->image));

            User::where('id', $id)->update(['image' => $imageName]);
            session()->flash('success', 'Profile Pic Updatd Successfully.');
            return response()->json([
                'status'    => true,
                'errors'    => []
            ]);

        } else {
            return response()->json([
                'status'    => false,
                'errors'    => $validator->errors()
            ]);
        }

    }

    public function logout(){
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function createJob(){
        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', 1)->get();
        return view('front.account.job.create', [
            'categories' => $categories, 
            'jobTypes'  => $jobTypes
        ]);
    }

    public function saveJob(Request $request){
        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'experience' => 'required',
            'company_name' => 'required|min:3|max:75',
        ];

        $validator = Validator::make($request->all(),$rules);

        if( $validator->passes() ){
            $job = new Job();
            $job->title = $request->title;
            $job->category_id  = $request->category;
            $job->job_type_id  = $request->jobType;
            $job->user_id  = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualifications = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->company_website;
            $job->save();

            session()->flash('success', 'Jobs added successfully');
            return response()->json([
                'status'    => true,
                'errors'    => []
            ]);

        } else {
            return response()->json([
                'status'    => false,
                'errors'    => $validator->errors()
            ]);
        }
    }

    public function myJobs(){
        $user_id = Auth::user()->id;
        $jobs = Job::where('user_id', $user_id)->with('jobType')->paginate(10);
        
        return view('front.account.job.my-jobs', [
            'jobs'  => $jobs
        ]);
    }


}
