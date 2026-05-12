<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Model
{
    use HasFactory; // Add this
    use SoftDeletes;
    protected $primaryKey = 'discussionId';
    protected $guarded = [];

    public function member() {
        return $this->belongsTo(Member::class, 'userId', 'userId');
    }

    public function problem() {
        return $this->belongsTo(Problem::class, 'problemId', 'problemId');
    }
}
