<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    private string $apiKey;
    private string $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
    }

    public function assist(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:5000',
            'mode' => 'required|in:summarize,improve,expand,tags,title',
            'content' => 'nullable|string',
        ]);

        $systemPrompt = $this->getSystemPrompt($request->mode);
        $userMessage = $request->prompt;

        if ($request->content) {
            $userMessage = "Note content:\n{$request->content}\n\nInstruction: {$request->prompt}";
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'Cloud Notes App',
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'google/gemini-2.0-flash-001',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'AI service unavailable. Please try again.'], 503);
            }

            $data = $response->json();
            $result = $data['choices'][0]['message']['content'] ?? '';

            return response()->json(['result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to connect to AI service.'], 500);
        }
    }

    public function chat(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
            'note_context' => 'nullable|string',
        ]);

        $messages = $request->messages;

        if ($request->note_context) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => "You are a helpful AI assistant for a notes application. The user is currently working on a note with this content:\n\n{$request->note_context}\n\nHelp them with questions, writing, organization, and ideas related to their notes."
            ]);
        } else {
            array_unshift($messages, [
                'role' => 'system',
                'content' => "You are a helpful AI assistant for a cloud notes application. Help users organize their thoughts, write better notes, brainstorm ideas, and stay productive. Be concise and friendly."
            ]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'Cloud Notes App',
            ])->post($this->apiUrl, [
                'model' => 'google/gemini-2.0-flash-001',
                'messages' => $messages,
                'max_tokens' => 1500,
                'temperature' => 0.8,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'AI service unavailable.'], 503);
            }

            $data = $response->json();
            $result = $data['choices'][0]['message']['content'] ?? '';

            return response()->json(['message' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to connect to AI.'], 500);
        }
    }

    private function getSystemPrompt(string $mode): string
    {
        return match ($mode) {
            'summarize' => 'You are a summarization expert. Provide a concise, clear summary of the given text in 2-3 sentences. Focus on the key points.',
            'improve' => 'You are a writing coach. Improve the given text by enhancing clarity, grammar, and flow while keeping the original meaning. Return only the improved text.',
            'expand' => 'You are a creative writer. Expand the given text with more detail, examples, and depth. Keep the same tone and style.',
            'tags' => 'You are a content organizer. Generate 3-7 relevant tags for the given note content. Return ONLY a JSON array of tag strings, like: ["tag1", "tag2", "tag3"]',
            'title' => 'You are a content expert. Generate a short, catchy, descriptive title for the given note content. Return ONLY the title, nothing else.',
            default => 'You are a helpful writing assistant.',
        };
    }
}
