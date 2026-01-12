<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ContactEmail;
use App\Models\ContactPhone;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'additional_file',
        'is_merged'
    ];

    public function customFields()
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function emails()
    {
        return $this->hasMany(ContactEmail::class);
    }

    public function phones()
    {
    return $this->hasMany(ContactPhone::class);
    }

    // public function mergedInto()
    // {
    // return $this->belongsTo(Contact::class, 'merged_into_id');
    // }
    public function mergeLogs() 
    { 
    return $this->hasMany(ContactMergeLog::class, 'master_contact_id'); 
    }
}
