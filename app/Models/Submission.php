<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model {
    use HasFactory;

    protected $primaryKey = 'submissionId';

    // Allow mass assignment for all merged columns
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'exit_code' => 'integer',
            'execution_time_ms' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Existing Relations
    public function member() {
        return $this->belongsTo(Member::class, 'userId', 'userId');
    }

    public function problem() {
        return $this->belongsTo(Problem::class, 'problemId', 'problemId');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'Accepted');
    }
}
