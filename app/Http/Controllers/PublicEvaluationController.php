<?php

namespace App\Http\Controllers;

use App\RegistrarEvaluationForm;
use App\RegistrarEvaluationResponse;
use Illuminate\Http\Request;

class PublicEvaluationController extends Controller
{
    public function show(string $token)
    {
        $evaluation = RegistrarEvaluationForm::where('public_token', $token)->first();

        if (!$evaluation || $evaluation->status !== 'Published') {
            return view('public.evaluation-unavailable');
        }

        return view('public.evaluation-form', [
            'evaluation' => $evaluation,
            'token' => $token,
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $evaluation = RegistrarEvaluationForm::where('public_token', $token)->first();

        if (!$evaluation || $evaluation->status !== 'Published') {
            return view('public.evaluation-unavailable');
        }

        $blocks = $evaluation->blocks ?: [];
        $rules = [];
        foreach ($blocks as $block) {
            foreach (($block['questions'] ?? []) as $question) {
                $rules['answers.' . $block['id'] . '_' . $question['id']] = 'required|integer|min:1|max:5';
            }
        }

        $validated = $request->validate($rules);

        RegistrarEvaluationResponse::create([
            'evaluation_form_id' => $evaluation->id,
            'answers' => $validated['answers'] ?? [],
            'submitted_at' => now(),
        ]);

        $evaluation->increment('responses');

        return view('public.evaluation-thank-you', [
            'evaluation' => $evaluation,
        ]);
    }
}
