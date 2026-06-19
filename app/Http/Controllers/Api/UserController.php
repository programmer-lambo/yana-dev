<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function followToggle($authorId)
    {
        try {
            $result = $this->userService->toggleFollow(Auth::id(), (int) $authorId);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'status' => $result['status']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function myFollowStats()
    {
        try {
            $currentUserId = request()->user('sanctum') ? request()->user('sanctum')->id : null;

            $data = $this->userService->getCurrentUserStatsAndStatus($currentUserId);

            return response()->json([
                'success' => true,
                'data'    => $data
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() == 404 ? 404 : 400;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $statusCode);
        }
    }
}