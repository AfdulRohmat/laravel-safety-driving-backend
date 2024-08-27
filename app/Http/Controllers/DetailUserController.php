<?php

namespace App\Http\Controllers;

use App\Http\Responses\CommonResponse;
use App\Models\DetailUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DetailUserController extends Controller
{
    public function getUser(Request $request)
    {
        try {
            $currentUserId =  Auth::user()->email;

            $user = User::where('email', $currentUserId)->with('detailUser')->firstOrFail();

            // Return success response with user data
            $successResponse = new CommonResponse(200, 'User retrieved successfully', $user, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function addOrUpdateDetailUserInfo(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255'],
                'nama_depan' => ['required', 'string', 'max:255'],
                'nama_belakang' => ['required', 'string', 'max:255'],
                'no_telepon' => ['required', 'string', 'max:20'],
                'tempat_lahir' => ['required', 'string', 'max:255'],
                'tanggal_lahir' => ['required', 'date'],
                'jenis_kelamin' => ['required', Rule::in(['Laki-Laki', 'Perempuan'])],
            ]);

            // Handle validation errors
            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun tidak valid, silahkan login ulang']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Create or update DetailUser
            DetailUser::updateOrCreate(
                ['user_id' => $user->id], // Unique key to identify the record
                [
                    'nama_depan' => $request->nama_depan,
                    'nama_belakang' => $request->nama_belakang,
                    'no_telepon' => $request->no_telepon,
                    'tempat_lahir' => $request->tempat_lahir,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'jenis_kelamin' => $request->jenis_kelamin
                ]
            );

            // Return the updated user with detail user info
            $userWithDetails = User::with('detailUser')->where('email', $request->email)->firstOrFail();

            $successResponse = new CommonResponse(200, 'Detail user berhasil ditambahkan atau diperbarui', $userWithDetails, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);

            //
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function getDetailUser(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => ['required', 'string', 'email', 'max:255'],
                ]
            );

            // Handle validation errors
            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Retrieve the user with the detailUser relationship
            $user = User::with('detailUser')->where('email', $request->email)->first();

            if (!$user) {
                $errorResponse = new CommonResponse(404, 'User not found', null, ['Akun tidak ditemukan']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            $successResponse = new CommonResponse(200, 'Detail user berhasil diambil', $user, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            $errorResponse = new CommonResponse(404, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function getAllUsers(Request $request)
    {
        try {
            $searchTerm = $request->input('search');

            // Build query
            $query = User::query();

            if ($searchTerm) {
                $query->where(function (Builder $query) use ($searchTerm) {
                    $query->where('email', 'like', "%{$searchTerm}%")
                        ->orWhere('username', 'like', "%{$searchTerm}%");
                });
            }

            // Execute query and get users
            $users = $query->get();

            // Return response with users data
            $successResponse = new CommonResponse(200, 'Users retrieved successfully', $users, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }
}
