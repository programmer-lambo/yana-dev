<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NoteService
{

    private function generateUniqueSlug(string $title, string $timestamp): string
    {
        $slugTitle = Str::slug($title);
        $hash = substr(hash('sha256', $slugTitle . '-' . $timestamp), 0, 16);

        return $slugTitle . '-' . $hash;
    }


    private function transformNote(Note $note, bool $bypassTruncation = false)
    {
        $note->uploaded_at = $note->created_at->translatedFormat('l, d F Y, H:i');
        $note->last_edited_at = $note->updated_at->translatedFormat('l, d F Y, H:i');

        unset($note->created_at);
        unset($note->updated_at);
        unset($note->category_id);
        unset($note->author_id);

        if (!$bypassTruncation && $note->body !== null) {
            $wordCount = Str::wordCount($note->body);

            if ($wordCount >= 50) {
                $note->body = Str::words($note->body, 30, '...');
            } else {
                $note->body = null;
            }
        }

        return $note;
    }

    private function formatPaginatorOutput(LengthAwarePaginator $paginator)
    {
        return [
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total_data'   => $paginator->total(),
            ],
            'links' => [
                'first_page_url' => $paginator->url(1),
                'last_page_url'  => $paginator->url($paginator->lastPage()),
                'next_page_url'  => $paginator->nextPageUrl(),
                'prev_page_url'  => $paginator->previousPageUrl(),
            ],
            'notes' => $paginator->items()
        ];
    }

    public function getAllSearchable(?int $page = null)
    {
        $paginator = Note::with(['author:id,name', 'category:id,name'])
            ->where('is_indexed', true)
            ->latest()
            ->paginate(10, ['*'], 'page', $page);

        $paginator->through(function ($note) {
            return $this->transformNote($note, false);
        });

        return $this->formatPaginatorOutput($paginator);
    }

    public function getNoteBySlug(string $slug)
    {
        $note = Note::with(['author:id,name', 'category:id,name'])
                ->where('slug', $slug)
                ->first();

        $note->uploaded_at = $note->created_at->translatedFormat('l, d F Y, H:i');
        $note->last_edited_at = $note->updated_at->translatedFormat('l, d F Y, H:i');

        unset($note->created_at);
        unset($note->updated_at);
        unset($note->category_id);
        unset($note->author_id);

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

    public function getNotesByAuthorId(int $authorId, ?int $currentUserId, ?int $page = null)
    {
        $authorExists = User::where('id', "=", $authorId)->exists();
        if (!$authorExists) {
            throw new \Exception("Author tidak ditemukan.", 404);
        }

        $isMutualFollowing = false;
        if ($currentUserId) {
            if ($currentUserId === $authorId) {
                $isMutualFollowing = true;
            } else {
                $youFollowAuthor = DB::table('followings')->where('follower_id', $currentUserId)->where('following_id', $authorId)->exists();
                $authorFollowsYou = DB::table('followings')->where('follower_id', $authorId)->where('following_id', $currentUserId)->exists();
                $isMutualFollowing = $youFollowAuthor && $authorFollowsYou;
            }
        }

        $paginator = Note::with(['author:id,name', 'category:id,name'])
            ->where('author_id', $authorId)
            ->latest()
            ->paginate(10, ['*'], 'page', $page);

        $paginator->through(function ($note) use ($isMutualFollowing) {
            if (!$isMutualFollowing && !$note->is_indexed) {
                $note->body = null;
                $note->slug = null;
                return $this->transformNote($note, true); 
            }

            $bypassTruncation = $isMutualFollowing; 
            return $this->transformNote($note, $bypassTruncation);
        });

        return $this->formatPaginatorOutput($paginator);
    }

    public function getNotesByCategoryId(int $categoryId, ?int $page = null)
    {
        $categoryExists = Category::where('id', "=", $categoryId)->exists();
        if (!$categoryExists) {
            throw new \Exception("Kategori tidak ditemukan.", 404);
        }

        $paginator = Note::with(['author:id,name', 'category:id,name'])
            ->where('category_id', $categoryId)
            ->where('is_indexed', true)
            ->latest()
            ->paginate(10, ['*'], 'page', $page);

        $paginator->through(function ($note) {
            return $this->transformNote($note, false);
        });

        return $this->formatPaginatorOutput($paginator);
    }
}