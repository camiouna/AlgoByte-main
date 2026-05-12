<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory; // Add this
    use SoftDeletes;
    protected $primaryKey = 'commentId';
    protected $guarded = [];

    public function member() {
        return $this->belongsTo(Member::class, 'userId', 'userId');
    }

    // A comment can belong to one of these two
    public function discussion() {
        return $this->belongsTo(Discussion::class, 'discussionId', 'discussionId');
    }

    public function sharedSolution() {
        return $this->belongsTo(SharedSolution::class, 'sharedSolutionId', 'solutionId');
    }
}
