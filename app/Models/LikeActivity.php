<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LikeActivity extends Model
{
    protected $table = 'like_activities'; // Laravel might expect 'like_activitys', so we specify this.
    protected $primaryKey = 'likeId';
    protected $guarded = [];
    use HasFactory; // Add this

    // Relationships
    public function member(): BelongsTo {
        return $this->belongsTo(Member::class, 'userId', 'userId');
    }

    public function discussion(): BelongsTo {
        return $this->belongsTo(Discussion::class, 'discussionId', 'discussionId');
    }

        public function comment(): BelongsTo {
            return $this->belongsTo(Comment::class, 'commentId', 'commentId');
        }

    public function sharedSolution(): BelongsTo {
        return $this->belongsTo(SharedSolution::class, 'sharedSolutionId', 'solutionId');
    }
}
