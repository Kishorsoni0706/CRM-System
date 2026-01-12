<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'custom_field_definition_id',
        'field_value'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function fieldDefinition()
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'custom_field_definition_id');
    }
}
