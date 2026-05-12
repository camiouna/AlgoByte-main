<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $primaryKey = 'reportId';

    protected $fillable = [
        'reporterId',
        'reviewerId',
        'problemId',
        'reason',
        'severity',
        'status',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'reporterId', 'userId');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'reviewerId', 'userId');
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class, 'problemId', 'problemId');
    }
}
