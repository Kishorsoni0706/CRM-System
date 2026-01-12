<?php
namespace App\Http\Controllers;

use App\Models\ContactMergeLog;

class MergeHistoryController extends Controller
{
    public function show($id)
    {
        $logs = ContactMergeLog::with('secondaryContact')
            ->where(function ($q) use ($id) {
                $q->where('master_contact_id', $id)
                  ->orWhere('secondary_contact_id', $id);
            })
            ->latest()
            ->get();

        return response()->json($logs);
    }
}
