<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NoteService
{

    private function generateUniqueSlug(string $title, string $timestamp): string
    {
        $slugTitle = Str::slug($title);
        $hash = substr(hash('sha256', $slugTitle . '-' . $timestamp), 0, 16);
        // $hash = hash('sha256', $stringToHash);

        return $slugTitle . '-' . $hash;
    }


    public function getAllSearchable()
    {
        return Note::with(['author:id,name', 'category:id,name'])
            ->where('is_indexed', true)
            ->latest()
            ->get();
    }

    public function getNoteBySlug(string $slug)
    {
        $note = Note::with(['author:id,name', 'category:id,name'])
                ->where('slug', $slug)
                ->first();

        if (!$note) {
            throw new \Exception("Note tidak ditemukan.", 404);
        }

        return $note;
    }

    public function createNote(array $data, int $authorId)
    {
        $curr_time = now();
        $data['slug'] = $this->generateUniqueSlug($data['title'], $curr_time->format('YmdHis'));
        $data['author_id'] = $authorId;
        $data['is_indexed'] = $data['is_indexed'] ?? true;
        $data["created_at"] = $curr_time->format("Y-m-d H:i:s");

        return Note::create($data);
    }

    public function updateNote(array $data, object $note, int $authorId)
    {
        if ($note->author_id !== $authorId) {
            throw new \Exception("Kamu tidak memiliki akses untuk mengubah catatan ini.", 403);
        }

        $note->update($data);
        return $note;
    }

    public function deleteNote(object $note, int $authorId)
    {
        if ($note->author_id !== $authorId) {
            throw new \Exception("Kamu tidak memiliki akses untuk menghapus catatan ini.", 403);
        }

        return $note->delete();
    }

    public function getNotesByAuthorId(int $authorId, ?int $currentUserId)
    {
        $authorExists = User::where('id', "=", $authorId)->exists();
        if (!$authorExists) {
            throw new \Exception("Author tidak ditemukan.", 404);
        }

        $isMutualFollowing = false;
    
        if ($currentUserId) {
            // 🚀 JIKA YANG AKSES ADALAH AUTHOR-NYA SENDIRI:
            if ($currentUserId === $authorId) {
                $isMutualFollowing = true;
            } else {
                // Jika orang lain, baru jalankan kueri cek saling follow
                $youFollowAuthor = DB::table('followings')
                    ->where('follower_id', $currentUserId)
                    ->where('following_id', $authorId)
                    ->exists();

                $authorFollowsYou = DB::table('followings')
                    ->where('follower_id', $authorId)
                    ->where('following_id', $currentUserId)
                    ->exists();

                $isMutualFollowing = $youFollowAuthor && $authorFollowsYou;
            }
        }

        $notes = Note::with(['author:id,name', 'category:id,name'])
            ->where('author_id', $authorId)
            ->latest()
            ->get();

        $processedNotes = $notes->map(function ($note) use ($isMutualFollowing) {
            if (!$isMutualFollowing && !$note->is_indexed) {
                $note->body = null;
                $note->slug = null;
            }
            
            return $note;
        });

        return $processedNotes;
    }
}