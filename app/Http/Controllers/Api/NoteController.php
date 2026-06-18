<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NoteController extends Controller
{
    protected $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);

            $notes = $this->noteService->getAllSearchable((int) $page);

            return response()->json([
                'success' => true,
                'message' => 'Detail catatan berhasil ditemukan.',
                'data' => $notes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function show(string $slug)
    {
        try {
            $note = $this->noteService->getNoteBySlug($slug);

            return response()->json([
                'success' => true,
                'message' => 'Detail catatan berhasil ditemukan.',
                'data' => $note
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                "is_indexed" => "required|boolean",
            ]);

            $note = $this->noteService->createNote($validated, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Catatan baru berhasil diterbitkan!',
                'data' => $note
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function update(Request $request, string $slug)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'body' => 'sometimes|required|string',
                'category_id' => 'sometimes|required|exists:categories,id',
                'is_indexed' => 'sometimes|boolean'
            ]);

            $note = $this->noteService->getNoteBySlug($slug);

            $updatedNote = $this->noteService->updateNote(
                $validated, 
                $note, 
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil diperbarui!',
                'data' => $updatedNote
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function destroy(string $slug)
    {
        try {
            $note = $this->noteService->getNoteBySlug($slug);

            $this->noteService->deleteNote($note, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function getByAuthor(string $authorId, Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $currentUserId = Auth::guard('sanctum')->id(); 

            $notes = $this->noteService->getNotesByAuthorId((int) $authorId, $currentUserId, (int) $page);

            return response()->json([
                'success' => true,
                'message' => 'Daftar catatan author berhasil dimuat.',
                'data' => $notes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function showNotes(string $id, Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $notes = $this->noteService->getNotesByCategoryId((int) $id, (int) $page);
            return response()->json([
                'success' => true,
                'message' => 'Daftar catatan berdasarkan kategori berhasil ditemukan.',
                'data' => $notes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
