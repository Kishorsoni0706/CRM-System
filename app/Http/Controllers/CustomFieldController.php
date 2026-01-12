<?php

namespace App\Http\Controllers;

use App\Models\CustomFieldDefinition;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    // Store a new custom field
    public function store(Request $request)
{
    $request->validate(['field_name' => 'required']);

    CustomFieldDefinition::create([
        'field_name' => $request->field_name,
        'field_type' => $request->field_type ?? 'text', // default
    ]);

    return response()->json(['success' => true]);
}


    // List all custom fields
    public function index()
    {
        $fields = CustomFieldDefinition::all();
        return response()->json($fields);
    }
}
