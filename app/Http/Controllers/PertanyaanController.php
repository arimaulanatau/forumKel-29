<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;


class PertanyaanController extends Controller
{
    public function index()
    {
        $questions = Question::with('user', 'votes')->withCount('answers')->latest()->paginate(10);
        return view('pertanyaan.index', compact('questions'));
    }

    public function create()
    {
        return view('pertanyaan.create');
    }

    public function store(Request $request)
    {
        request()->validate([
            'title' => 'required',
            'content' => 'required',
            'tag' => 'nullable',
        ]);

        $thread = new Question;
        $thread->user_id = auth()->user()->id;
        $thread->title = request('title');
        $thread->content = request('content');
        $thread->tag = request('tag');
        $thread->save();

        session()->flash('success', 'Pertanyaan telah tersimpan');
        return redirect('/pertanyaan');
    }

    public function show($questionId)
    {
        $question = Question::with('user', 'votes')->withCount('answers')->findOrFail($questionId);
        return view('pertanyaan.show', compact('question'));
    }

    public function edit($questionId)
    {
        $question = Question::with('user')->findOrFail($questionId);
        if ($question->user_id !== auth()->user()->id) {
            return abort(403);
        }
        return view('pertanyaan.edit', compact('question'));
    }

    public function update(Request $request, $questionId)
    {
        $question = Question::findOrFail($questionId);
        if ($question->user_id !== auth()->user()->id) {
            return abort(403);
        }

        request()->validate([
            'title' => 'required',
            'content' => 'required',
            'tag' => 'nullable',
        ]);

        $question->title = request('title');
        $question->content = request('content');
        $question->tag = request('tag');
        $question->save();

        session()->flash('success', 'Pertanyaan telah diperbarui');
        return redirect('/pertanyaan');
    }

    public function destroy($questionId)
    {
        $question = Question::findOrFail($questionId);
        if ($question->user_id !== auth()->user()->id) {
            return abort(403);
        }

        $question->answers()->delete();
        $question->delete();

        session()->flash('success', 'Pertanyaan telah dihapus');
        return redirect('/pertanyaan');
    }

    public function upvote($questionId)
    {
        $question = Question::findOrFail($questionId);
        $question->upvote();

        return redirect()->back();
    }

    public function downvote($questionId)
    {
        $question = Question::findOrFail($questionId);

        $user = auth()->user();
        $isAllowedToDownvote = $user->isAllowedToDownvote();
        if (!$isAllowedToDownvote) {
            Alert::toast('Maaf anda tidak bisa melakukan downvote', 'error');
            return redirect()->back();
        }

        $question->downvote();
        return redirect()->back();
    }
}
