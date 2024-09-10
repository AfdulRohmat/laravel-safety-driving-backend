<?php

namespace App\Http\Controllers;

use App\Enums\GroupRole;
use App\Http\Responses\CommonResponse;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    public function createGroup(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'nama_group' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the user by email
            $user = User::where('email', $currentUserEmail)->first();

            if (!$user) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun tidak valid, silahkan login ulang']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Create the group
            $group = new Group();
            $group->name = $request->nama_group;
            $group->description = $request->description;
            $group->created_by = $user->id;
            $group->save();

            // Create the group member with role ADMIN_GROUP
            $groupMember = new GroupMember();
            $groupMember->group_id = $group->id;
            $groupMember->user_id = $user->id;
            $groupMember->role = GroupRole::ADMIN_GROUP;
            $groupMember->save();

            // Return the created group
            $successResponse = new CommonResponse(200, 'Group berhasil dibuat', $group, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function addUserToGroupMemberByUsername(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'group_id' => 'required|integer|exists:groups,id',
                'role' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the user by ID
            $user = User::find($request->user_id);
            if (!$user) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the group by ID
            $group = Group::find($request->group_id);
            if (!$group) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Group tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Check if the user is already a member of the group
            $userAlreadyInGroup = GroupMember::where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->first();

            if ($userAlreadyInGroup) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['User sudah terdaftar dalam grup yang dipilih']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Create a new GroupMember instance
            $groupMember = new GroupMember();
            $groupMember->user_id = $user->id;
            $groupMember->group_id = $group->id;

            // Role assignment using switch-case
            switch ($request->role) {
                case 'ROLE_DRIVER':
                    $groupMember->role = GroupRole::DRIVER;
                    break;
                case 'ROLE_COMPANY':
                    $groupMember->role = GroupRole::COMPANY;
                    break;
                case 'ROLE_FAMILY':
                    $groupMember->role = GroupRole::FAMILY;
                    break;
                case 'ROLE_MEDIC':
                    $groupMember->role = GroupRole::MEDIC;
                    break;
                case 'ROLE_KNKT':
                    $groupMember->role = GroupRole::KNKT;
                    break;
                case 'ROLE_ADMIN_GROUP':
                    $groupMember->role = GroupRole::ADMIN_GROUP;
                    break;
                default:
                    $groupMember->role = GroupRole::USER_GROUP;
                    break;
            }

            // Save the group member
            $groupMember->save();

            // Return success response
            $successResponse = new CommonResponse(200, 'User berhasil ditambahkan ke grup', $groupMember, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function removeUserFromGroupMember(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'group_id' => 'required|integer|exists:groups,id',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the user by ID
            $user = User::find($request->user_id);
            if (!$user) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the group by ID
            $group = Group::find($request->group_id);
            if (!$group) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Group tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Check if the user is a member of the group
            $userInGroup = GroupMember::where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->first();

            if (!$userInGroup) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['User tidak terdaftar dalam grup yang dipilih']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Delete the group member
            $userInGroup->delete();

            // Return success response
            $successResponse = new CommonResponse(200, 'User successfully removed from group', null, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function getGroupsByUserLogin(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            // Find the user by email
            $user = User::where('email', $currentUserEmail)->first();
            if (!$user) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun tidak valid, silahkan login ulang']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Get groups where the user is a member
            $groups = GroupMember::with('group')
                ->where('user_id', $user->id)
                ->get();

            $successResponse = new CommonResponse(200, 'Group members retrieved successfully', $groups, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function getDetailGroup(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            // Validate the request
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|integer|exists:groups,id',
            ]);

            // Handle validation errors
            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the group and include its members and user details
            $group = Group::with(['members.user:id,username,email'])
                ->where('id', $request->group_id)
                ->first();

            if (!$group) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Group tidak ditemukan']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            $successResponse = new CommonResponse(200, 'Group detail retrieved successfully', $group, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }
}
