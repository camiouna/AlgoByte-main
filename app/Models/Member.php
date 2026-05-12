<?php

namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable

{

    use HasFactory;

    use SoftDeletes;
    protected $primaryKey = 'userId';
    protected $guarded = []; // Allows all fields to be filled

    public function problems() {
        return $this->hasMany(Problem::class, 'creatorId', 'userId');
    }
    public function submissions() {
    return $this->hasMany(Submission::class, 'userId', 'userId');
}

public function sharedSolutions() {
    return $this->hasMany(SharedSolution::class, 'userId', 'userId');
}
public function discussions() {
    return $this->hasMany(Discussion::class, 'userId', 'userId');
}

public function comments() {
    return $this->hasMany(Comment::class, 'userId', 'userId');
}


    public function solvedProblems()
    {
        return Problem::whereHas('submissions', function ($query) {
            $query->where('userId', $this->userId)
                  ->successful();
        });
    }

    public function attemptedProblems()
    {
        return Problem::whereHas('submissions', function ($query) {
            $query->where('userId', $this->userId);
        })->whereDoesntHave('submissions', function ($query) {
            $query->where('userId', $this->userId)
                  ->successful();
        });
    }
}
