<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ContactMergeLog;
use Illuminate\Support\Facades\DB;

class ContactMergeService
{
    public function merge(Contact $master, Contact $secondary)
    {
        DB::transaction(function () use ($master, $secondary) {

            // Snapshot secondary contact
            $secondaryData = [
                'id' => $secondary->id,
                'name' => $secondary->name,
                'email' => $secondary->email,
                'phone' => $secondary->phone,
                'gender' => $secondary->gender,
                'address' => $secondary->address,
                'share_code' => $secondary->share_code,
            ];

            // Custom fields snapshot
            $customFields = $secondary->customFields
                ->mapWithKeys(fn ($cf) => [
                    $cf->fieldDefinition->field_name => $cf->field_value
                ])
                ->toArray();

            $secondaryData['custom_fields'] = $customFields;

            /**
             * EMAIL DEDUPLICATION
             */
            $mergedEmails = [];
            foreach ($secondary->emails as $email) {
                $exists = $master->emails()
                    ->where('email', $email->email)
                    ->exists();

                if (!$exists) {
                    $email->update(['contact_id' => $master->id]);
                    $mergedEmails[] = $email->email;
                }
            }

            /**
             * PHONE DEDUPLICATION
             */
            $mergedPhones = [];
            foreach ($secondary->phones as $phone) {
                $exists = $master->phones()
                    ->where('phone', $phone->phone)
                    ->exists();

                if (!$exists) {
                    $phone->update(['contact_id' => $master->id]);
                    $mergedPhones[] = $phone->phone;
                }
            }

            /**
             * CUSTOM FIELD POLICY
             * - Keep MASTER value
             * - Log SECONDARY value if conflict
             */
            $ignoredCustomFields = [];

            foreach ($secondary->customFields as $field) {
                $existing = $master->customFields()
                    ->where('custom_field_definition_id', $field->custom_field_definition_id)
                    ->first();

                if (!$existing) {
                    $master->customFields()->create([
                        'custom_field_definition_id' => $field->custom_field_definition_id,
                        'field_value' => $field->field_value,
                    ]);
                } else {
                    if ($existing->field_value !== $field->field_value) {
                        $ignoredCustomFields[$field->fieldDefinition->field_name] = [
                            'kept' => $existing->field_value,
                            'ignored' => $field->field_value,
                        ];
                    }
                }
            }

            /**
             * MERGE LOG
             */
            ContactMergeLog::create([
                'master_contact_id' => $master->id,
                'secondary_contact_id' => $secondary->id,
                'secondary_contact_data' => $secondaryData,
                'merged_emails' => $mergedEmails,
                'merged_phones' => $mergedPhones,
                'merged_custom_fields' => $customFields,
                'ignored_custom_fields' => $ignoredCustomFields,
            ]);

            // Mark secondary as merged
            $secondary->update(['is_merged' => true]);
        });
    }
}
