<?php

namespace App\Domain\Leads;

use App\Models\LegalDocument;
use App\Models\User;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'message',
        'source',
        'form_type',
        'page_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'status',
        'assigned_to',
        'manager_comment',
        'consent_given_at',
        'privacy_document_id',
        'privacy_document_version',
    ];

    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'form_type' => LeadFormType::class,
            'status' => LeadStatus::class,
            'consent_given_at' => 'datetime',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function privacyDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'privacy_document_id');
    }

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }
}
