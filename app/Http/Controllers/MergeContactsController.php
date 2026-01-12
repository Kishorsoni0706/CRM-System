<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Services\ContactMergeService;

class MergeContactsController extends Controller
{
    public function merge(Request $request, ContactMergeService $mergeService)
    {
        $request->validate([
            'master_id' => 'required|exists:contacts,id',
            'secondary_id' => 'required|exists:contacts,id|different:master_id',
        ]);

        $master = Contact::with(['emails', 'phones', 'customFields.fieldDefinition'])
            ->findOrFail($request->master_id);

        $secondary = Contact::with(['emails', 'phones', 'customFields.fieldDefinition'])
            ->findOrFail($request->secondary_id);

        $mergeService->merge($master, $secondary);

        return response()->json(['message' => 'Contacts merged successfully']);
    }
}
