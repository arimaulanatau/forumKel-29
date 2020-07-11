<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use App\Models\Question;
use RealRashid\SweetAlert\Facades\Alert;


class JawabanController extends Controller
{

    public function index($threadId)
    {
        $thread = Thread::with('user', 'replies')->findOrFail($threadId);
        return view('jawaban.index', compact('thread'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request, $threadId)
    {
        $question = Question::findOrFail($threadId);

        request()->validate([
            'content' => 'required',
        ]);

        $answer = new Answer;
        $answer->question_id = $question->id;
        $answer->user_id = auth()->user()->id;
        $answer->content = request('content');
        $answer->save();

        session()->flash('success', 'Jawaban telah tersimpan');
        return redirect()->back();
    }

    public function show(Answer $answer)
    {
        //
    }

    public function edit(Answer $answer)
    {
        //
    }

    public function update(Request $request, Answer $answer)
    {
        //
    }

    public function destroy(Answer $answer)
    {
        //
    }

    public function upvote($answerId)
    {
        $answer = Answer::findOrFail($answerId);
        $answer->upvote();

        return redirect()->back();
    }

    public function downvote($answerId)
    {
        $answer = Answer::findOrFail($answerId);

        $user = auth()->user();
        $isAllowedToDownvote = $user->isAllowedToDownvote();
        if (!$isAllowedToDownvote) {
            Alert::toast('Anda tidak bisa melakukan downvote', 'error');
            return redirect()->back();
        }

        $answer->downvote();
        return redirect()->back();
    }
}
