<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNoteRequest;
use App\Services\NoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NoteController extends Controller
{
    protected $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function index()
    {
        try {
            $note = $this->noteService->getAllSearchable();

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

    public function update(UpdateNoteRequest $request, string $slug)
    {
        try {
            $note = $this->noteService->getNoteBySlug($slug);

            $updatedNote = $this->noteService->updateNote(
                $request->validated(), 
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

    public function getByAuthor(string $authorId)
    {
        try {
            $currentUserId = Auth::guard('sanctum')->id(); 

            $notes = $this->noteService->getNotesByAuthorId((int) $authorId, $currentUserId);

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
}
