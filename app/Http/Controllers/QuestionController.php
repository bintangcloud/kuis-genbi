<?php
namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function game() {
        $questions = Question::all();
        return view('game', compact('questions'));
    }
    public function index() {
        return view('admin.index', ['questions' => Question::latest()->get()]);
    }
    public function create() {
        return view('admin.create');
    }
    public function store(Request $request) {
        Question::create($request->all());
        return redirect()->route('admin.index');
    }
    public function edit(Question $question) {
        return view('admin.create', compact('question')); // Pakai form yang sama
    }
    public function update(Request $request, Question $question) {
        $question->update($request->all());
        return redirect()->route('admin.index');
    }
    public function destroy(Question $question) {
        $question->delete();
        return redirect()->route('admin.index');
    }
}