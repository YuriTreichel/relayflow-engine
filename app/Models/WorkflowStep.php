<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'type',
        'config',
        'order',
        'next_step_id',
        'on_failure_step_id',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function nextStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'next_step_id');
    }

    public function onFailureStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'on_failure_step_id');
    }
}