<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Mail\RegistrationVerification;
use App\Models\User;
use App\Models\UserInformation;
use Crypt;
use DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    //use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'agb' => ['required', 'accepted'],
        ];

        // Only add reCAPTCHA validation in production
        if (app()->environment('production')) {
            $rules['g-recaptcha-response'] = 'recaptcha';
        }

        return Validator::make($data, $rules);
    }



    /*public function showRegistrationForm()
    {
        return view('auth.register');
    }*/
    
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $locale = app()->getLocale();
        $request->merge(['language' => $locale]);

        try {
            // Use database transaction to ensure data consistency
            $user = DB::transaction(function () use ($request) {
                // Create UserInformation record first
                $userInformation = UserInformation::create([
                    'request_as_guide' => false,
                ]);
                
                // Create user with the user_information_id
                $user = User::create([
                    'firstname' => mb_convert_encoding($request->firstname, 'UTF-8', 'auto'),
                    'lastname' => mb_convert_encoding($request->lastname, 'UTF-8', 'auto'),
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'language' => $request->language ?? app()->getLocale(),
                    'user_information_id' => $userInformation->id,
                ]);

                // Fire registered event
                event(new Registered($user));

                return $user;
            });
            
            // Log the user in
            Auth::login($user);
        } catch (\Exception $e) {            
            // Check for specific database errors
            if ($e instanceof \Illuminate\Database\QueryException) {
                Log::error('Database error details: ' . $e->getMessage());
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => ['Registration failed. Please try again.']]
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['general' => 'Registration failed. Please try again.'])
                ->withInput();
        }

        // Send verification email
        // Mail::send(new RegistrationVerification($user));

        if ($request->ajax()) {
            // Check if user is currently on the checkout page
            $referer = $request->header('referer');
            $isOnCheckoutPage = $referer && str_contains($referer, '/checkout');
            
            $response = [
                'success' => true,
                'message' => 'Registration successful!',
                'redirect' => $isOnCheckoutPage ? null : route('profile.index')
            ];
            
            return response()->json($response);
        }

        return redirect($this->redirectPath())
            ->with('success-message', 'Success!');
    }

    protected function guard()
    {
        return Auth::guard();
    }
    
    protected function registered(Request $request, $user)
    {
        //
    }
    
    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        Auth::guard('employees')->logout();
        Auth::logout();

        return url('/login');
    }

    public function verfication(Request $request)
    {
        if(!$request->has('hash')) {
            abort(404);
        }

        $id = Crypt::decrypt($request->hash);
        $user = User::where('id', $id)->whereNull('email_verified_at')->first();

        if (is_null($user)) {
            User::where('id', $id)->update([ 'email_verified_at' => DB::raw('NOW()') ]);
        }

        return view('auth.register-verification');
    }
}
