<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discussion;

class DiscussionController extends Controller
{
    public function upvote(Request $request, Discussion $discussion)
    {
        $userId = auth()->id();
        if($discussion->likes()->where('userId', $userId)->exists()) {
            return response()->json(['message' => 'Already liked'], 400);
        }

        $discussion->likes()->create(['userId' => $userId]);
        return response()->json(['message' => 'Upvoted successfully']);
    }

    public function downvote(Request $request, Discussion $discussion)
    {
        $userId = auth()->id();
        $like = $discussion->likes()->where('userId', $userId)->first();
        if($like) {
            $like->delete();
        }
        return response()->json(['message' => 'Downvoted successfully']);
    }
}

