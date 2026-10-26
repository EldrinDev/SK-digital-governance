<?php

namespace App\Models;

use App\Models\Concerns\HasCouncilApproval;
use App\PlanStatus;
use Database\Factories\AbyipAmendmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A change to an approved ABYIP. While it's a draft the PPAs can be edited;
 * recording the council's resolution approves it and locks the ABYIP again.
 */
#[Fillable(['reason'])]
class AbyipAmendment extends Model
{
    /** @use HasFactory<AbyipAmendmentFactory> */
    use HasCouncilApproval, HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PlanStatus::class,
            'approved_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Abyip, $this>
     */
    public function abyip(): BelongsTo
    {
        return $this->belongsTo(Abyip::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }
}
