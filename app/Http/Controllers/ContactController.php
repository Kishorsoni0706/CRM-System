<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // INDEX: show contacts & handle AJAX filtering
    public function index(Request $request)
    {
        // ❌ REMOVED hard-coded is_merged filter
        // ✅ Now applied conditionally
        $query = Contact::with('customFields.fieldDefinition');

        // 🔥 This makes "Show merged contacts" work
        if (!$request->boolean('show_merged')) {
            $query->where('is_merged', false);
        }

        if ($request->ajax()) {
            if ($request->filled('name')) {
                $query->where('name', 'like', "%{$request->name}%");
            }

            if ($request->filled('email')) {
                $query->where('email', 'like', "%{$request->email}%");
            }

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            $contacts = $query->get();

            // Append custom fields for JSON response
            $contacts->each(function ($contact) {
                $contact->custom_fields_values = $contact->customFields->mapWithKeys(function ($cf) {
                    return [$cf->custom_field_definition_id => $cf->field_value];
                });
            });

            return response()->json($contacts);
        }

        // Non-AJAX (initial page load)
        $contacts = $query->get();
        $customFields = CustomFieldDefinition::all();

        return view('contacts.index', compact('contacts', 'customFields'));
    }

    // STORE: create a new contact
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:contacts',
            'phone'  => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        if ($request->hasFile('additional_file')) {
            $validated['additional_file'] = $request->file('additional_file')->store('documents', 'public');
        }

        $contact = Contact::create($validated);

        foreach ($request->custom_fields ?? [] as $fieldId => $value) {
            CustomFieldValue::create([
                'contact_id' => $contact->id,
                'custom_field_definition_id' => $fieldId,
                'field_value' => $value,
            ]);
        }

        return response()->json(['success' => true]);
    }

    // SHOW: return single contact with custom fields (for edit)
    public function show(Contact $contact)
    {
        $contact->load('customFields.fieldDefinition');

        $customFieldsValues = $contact->customFields->mapWithKeys(function ($cf) {
            return [$cf->custom_field_definition_id => $cf->field_value];
        });

        return response()->json([
            'contact' => $contact,
            'custom_fields' => $customFieldsValues,
        ]);
    }

    // UPDATE: update contact with custom fields
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:contacts,email,' . $contact->id,
            'phone'  => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        if ($request->hasFile('additional_file')) {
            $validated['additional_file'] = $request->file('additional_file')->store('documents', 'public');
        }

        $contact->update($validated);

        foreach ($request->custom_fields ?? [] as $fieldId => $value) {
            $contact->customFields()->updateOrCreate(
                ['custom_field_definition_id' => $fieldId],
                ['field_value' => $value]
            );
        }

        return response()->json(['success' => true]);
    }

    // DELETE: remove contact
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(['success' => true]);
    }
}
