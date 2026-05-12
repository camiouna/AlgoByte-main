<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestCase extends Model
{
    use SoftDeletes;
    use HasFactory; // Add thi-s
    protected $primaryKey = 'testCaseId';

    protected $fillable = [
        'problemId',
        'input',
        'expected_output',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'problemId' => 'integer',
        ];
    }

    

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class, 'problemId', 'problemId');
    }
}
