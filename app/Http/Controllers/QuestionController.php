<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;

class QuestionController extends Controller
{
    public function game()
    {
        $questions = \App\Models\Question::all();
        return view('game', compact('questions'));
    }   

    public function index()
    {
        return view('admin.index', ['questions' => Question::latest()->get()]);
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'q' => 'required',
            'a' => 'required',
            'b' => 'required',
            'ans' => 'required|in:1,2',
            'ex' => 'required',
        ]);

        Question::create($request->all());

        return redirect()->route('questions.index')->with('success', 'Question created successfully.');
    }

    public function edit(Question $question)
    {
        return view('admin.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'q' => 'required',
            'a' => 'required',
            'b' => 'required',
            'ans' => 'required|in:1,2',
            'ex' => 'required',
        ]);

        $question->update($request->all());

        return redirect()->route('questions.index')->with('success', 'Question updated successfully.');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('questions.index')->with('success', 'Question deleted successfully.');
    }
}
