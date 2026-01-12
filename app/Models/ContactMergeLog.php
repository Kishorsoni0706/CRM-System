<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMergeLog extends Model
{
    protected $fillable = [
        'master_contact_id',
        'secondary_contact_id',
        'secondary_contact_data', // ✅ REQUIRED
        'merged_emails',
        'merged_phones',
        'merged_custom_fields',
    ];

    protected $casts = [
        'secondary_contact_data' => 'array', // ✅ REQUIRED
        'merged_emails' => 'array',
        'merged_phones' => 'array',
        'merged_custom_fields' => 'array',
    ];

    public function secondaryContact()
    {
        return $this->belongsTo(Contact::class, 'secondary_contact_id');
    }
}
