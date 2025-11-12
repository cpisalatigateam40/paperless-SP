<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Department;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function syncUser(Request $request)
    {
        try {
            $data = $request->json()->all();


            if (empty($data['user'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid payload'
                ], 400);
            }


            $user = $data['user'];


            $area = Area::firstWhere('name', $user['department']['plant']);
            if (!$area) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Area not found'
                ], 404);
            }


            $userData = [
                'username' => $user['username'] ?? null,
                'name' => $user['name'] ?? null,
                'email' => $user['email'] ?? null,
                'password' => $user['password'] ?? null,
                'area_uuid' => $area->uuid,
                'plant_uuid' => null,
                'department_uuid' => null,
            ];


            $existingUser = User::where('uuid', $user['uuid'])
            ->orWhere('email', $user['email'] )
            ->orWhere('username', $user['username'] )
            ->first();


            if ($existingUser) {
                
                $existingUser->update($userData);
                if (!empty($user['project_role']['role'])) {
                    $existingUser->assignRole($user['project_role']['role']);
                }
            } else {
                $newUser = User::create(array_merge(['uuid' => $user['uuid']], $userData));
                if (!empty($user['project_role']['role'])) {
                    $newUser->assignRole($user['project_role']['role']);
                }
            }


            return response()->json([
                'status' => 'success',
                'message' => 'User synced successfully: ' . $user['uuid']
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() // only the message, no trace/HTML
            ], 200);
        }
    }


    public function desyncUser(Request $request)
    {
        try {
            $data = $request->json()->all();


            if (empty($data['user_uuid'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid payload'
                ], 400);
            }


            $userUuid = $data['user_uuid'];
            $user = User::where('uuid', $userUuid)->first();


            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }


            $deleted = $user->delete();


            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => "User desynced successfully: {$userUuid}"
                ]);
            }


            return response()->json([
                'status' => 'error',
                'message' => 'Failed to desync user'
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 200);
        }
    }

}