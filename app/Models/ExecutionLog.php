<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'workflow_step_id',
        'status',
        'input_payload',
        'output_payload',
        'error_message',
    ];

    protected $casts = [
        'input_payload' => 'array',
        'output_payload' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }
}