<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Problem extends Model {
    use HasFactory;
    use SoftDeletes;
    protected $primaryKey = 'problemId';
    protected $guarded = [];

    public function creator() {
        return $this->belongsTo(Member::class, 'creatorId', 'userId');
    }

    public function discussion() {
        return $this->hasMany(Discussion::class, 'problemId', 'problemId');
    }
    public function testCases() {
        return $this->hasMany(TestCase::class, 'problemId', 'problemId');
    }

    public function submissions() {
        return $this->hasMany(Submission::class, 'problemId', 'problemId');
    }
    public function sharedSolutions() {
        return $this->hasMany(SharedSolution::class, 'problemId', 'problemId');
    }
}
