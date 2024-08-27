<?php

namespace App\Http\Controllers;

use App\Http\Responses\CommonResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\ActivationCodeMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register User
    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => ['required', 'max:100',  Rule::unique(User::class)],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
                'password' => ['required', 'min:4', 'max:255'],
            ]);

            // generate activation code
            $activationCode = rand(100000, 999999);

            // send to email via smtp
            Mail::to($request->email)->send(new ActivationCodeMail($activationCode));

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'is_verified' => false,
                'activation_code' => $activationCode,
                'password' =>  bcrypt($request->password),
                'detail_user_id' => null
            ]);

            $responseData = [
                'user_id' => $user->id,
                'email' => $user->email,
                'activation_code' => $activationCode,
            ];

            $successResponse = new CommonResponse(201, 'Proses registrasi berhasil. Silahkan cek email anda', $responseData, null);

            return response()->json($successResponse->toArray(), $successResponse->statusCode);

            // 
        } catch (ValidationException $e) {
            // Handle validation errors
            $errorResponse = new CommonResponse(422, 'Validation error', null, $e->errors());
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function login(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|email|max:255',
                'password' => 'required|min:4',
            ]);

            // Handle validation errors
            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            $user = User::where('email', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'username' => ['username/email yang dimasukan salah.'],
                    'password' => ['Password yang dimasukan salah.'],
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $user['accessToken'] = $token;

            $successResponse = new CommonResponse(201, 'Proses registrasi berhasil. Silahkan cek email anda', $user, null);

            return response()->json($successResponse->toArray(), $successResponse->statusCode);

            // 
        } catch (\Exception $e) {
            $errorResponse = new CommonResponse(404, 'Proses gagal', null, ['User not found']);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function activateAccount(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'activation_code' => 'required|numeric|digits:6',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->firstOrFail();

            // Check if the account is already verified
            if ($user->is_verified) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun sudah terverifikasi. Silahkan login']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Validate the activation code
            if ($user->activation_code == $request->activation_code) {
                // Activate the account
                $user->is_verified = true;
                $user->save();

                $successResponse = new CommonResponse(200, 'Aktivasi akun berhasil, silahkan login', [
                    'email' => $user->email
                ], null);

                return response()->json($successResponse->toArray(), $successResponse->statusCode);
            } else {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Kode aktivasi tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }
        } catch (\Exception $e) {
            $errorResponse = new CommonResponse(404, 'Proses gagal', null, ['User not found']);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function resendActivationCode(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun belum terdaftar. Silahkan register']);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }

        // Check if the account is already verified
        if ($user->is_verified) {
            $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun sudah terverifikasi. Silahkan login']);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }

        // Generate a new activation code
        $activationCode = rand(100000, 999999);

        // Update the user's activation code
        $user->activation_code = $activationCode;
        $user->save();

        // Send the activation code via email
        Mail::to($user->email)->send(new ActivationCodeMail($activationCode));

        // Prepare the success response
        $successResponse = new CommonResponse(200, 'Activation Code berhasil dikirim ulang, Mohon check email anda', [
            'email' => $user->email
        ], null);

        return response()->json($successResponse->toArray(), $successResponse->statusCode);
    }
}
