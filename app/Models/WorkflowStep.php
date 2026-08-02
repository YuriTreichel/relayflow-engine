<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'type',
        'order',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $table->belongsTo(Workflow::class);
    }
}
