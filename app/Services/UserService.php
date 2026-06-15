<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function toggleFollow(int $currentUserId, int $targetAuthorId)
    {
        if ($currentUserId === $targetAuthorId) {
            throw new \Exception("Kamu tidak bisa mem-follow dirimu sendiri.", 400);
        }

        $authorExists = User::where('id', '=',$targetAuthorId)->exists();
        if (!$authorExists) {
            throw new \Exception("Author yang ingin kamu follow tidak ditemukan.", 404);
        }

        $existingFollow = DB::table('followings')
            ->where('follower_id', $currentUserId)
            ->where('following_id', $targetAuthorId);

        if ($existingFollow->exists()) {
            $existingFollow->delete();
            return ['status' => 'unfollowed', 'message' => 'Berhasil berhenti mengikuti author ini.'];
        } else {
            DB::table('followings')->insert([
                'follower_id' => $currentUserId,
                'following_id' => $targetAuthorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return ['status' => 'followed', 'message' => 'Berhasil mengikuti author ini!'];
        }
    }
}