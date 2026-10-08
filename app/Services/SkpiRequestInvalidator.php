<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SkpiRequestInvalidator
{
    public function invalidateFor(User $user): int
    {
        $requests = $user->skpiRequests()->whereIn('status', ['pending', 'issued'])->get();

        foreach ($requests as $request) {
            if ($request->docx_path) {
                Storage::disk('local')->delete($request->docx_path);
            }
            $request->update([
                'status' => 'rejected',
                'reviewed_by' => null,
                'admin_note' => null,
                'document_number' => null,
                'docx_path' => null,
                'snapshot' => null,
                'issued_at' => null,
            ]);
        }

        return $requests->count();
    }
}
