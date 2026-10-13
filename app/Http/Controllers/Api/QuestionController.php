<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Publik: daftar semua soal untuk halaman game.
     */
    public function index()
    {
        return response()->json(Question::orderBy('id')->get());
    }

    /**
     * Publik: satu soal.
     */
    public function show(Question $question)
    {
        return response()->json($question);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'q' => ['required', 'string'],
            'a' => ['required', 'string'],
            'b' => ['required', 'string'],
            'ans' => ['required', 'integer', 'in:0,1'],
            'ex' => ['nullable', 'string'],
        ]);

        return response()->json(Question::create($data), 201);
    }

    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'q' => ['required', 'string'],
            'a' => ['required', 'string'],
            'b' => ['required', 'string'],
            'ans' => ['required', 'integer', 'in:0,1'],
            'ex' => ['nullable', 'string'],
        ]);

        $question->update($data);

        return response()->json($question);
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(['message' => 'Soal dihapus.']);
    }
}
