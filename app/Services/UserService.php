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

    public function getAuthorStatsAndStatus(int $authorId, ?int $currentUserId)
    {
        $author = User::find($authorId);
        if (!$author) {
            throw new \Exception("Author tidak ditemukan.", 404);
        }

        $followersCount = DB::table('followings')->where('following_id', $authorId)->count();
        $followingCount = DB::table('followings')->where('follower_id', $authorId)->count();
        $notesCount     = DB::table('notes')->where('author_id', $authorId)->count();

        $isFollowing = false;
        if ($currentUserId) {
            $isFollowing = DB::table('followings')
                ->where('follower_id', $currentUserId)
                ->where('following_id', $authorId)
                ->exists();
        }

        return [
            'author_id'   => $authorId,
            'author_name' => $author->name,
            'stats' => [
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
                'notes_count'     => $notesCount,
            ],
            'auth_user_status' => [
                'is_following' => $isFollowing,
            ]
        ];
    }

    public function getCurrentUserStatsAndStatus(int $currentUserId)
    {
        $author = User::find($currentUserId);
        if (!$author) {
            throw new \Exception("Author tidak ditemukan.", 404);
        }

        $followersCount = DB::table('followings')->where('following_id', $currentUserId)->count();
        $followingCount = DB::table('followings')->where('follower_id', $currentUserId)->count();
        $notesCount     = DB::table('notes')->where('author_id', $currentUserId)->count();

        return [
            'author_id'   => $currentUserId,
            'author_name' => $author->name,
            'stats' => [
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
                'notes_count'     => $notesCount,
            ]
        ];
    }
}