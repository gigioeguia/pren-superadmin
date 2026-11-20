<?php

namespace App\Services;

use App\Enums\Channel;
use App\Helpers\HasUploader;
use App\Models\Support;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SupportService
{
    use HasUploader;

    public function storeMessage(array $data, Channel $channel, ?User $actor, ?UploadedFile $file = null): Support
    {
        $admin = User::where('role', 'superadmin')->first();

        $receiverId = $channel === Channel::Admin ? ($data['receiver_id'] ?? null) : $admin?->id;
        $senderId = $actor?->id;

        // find parent conversation
        $parent = null;
        if (! empty($data['id'])) {
            $parent = Support::find($data['id']);
        } else {
            $parent = Support::where(function ($q) use ($senderId, $receiverId) {
                $q->where('sender_id', $senderId)->where('receiver_id', $receiverId);
            })->orWhere(function ($q) use ($senderId, $receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', $senderId);
            })->orderBy('created_at', 'asc')->first();
        }

        $filePath = null;
        $fileExtension = null;
        $fileSize = null;
        if ($file) {
            $filePath = $this->uploadFile($file);
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
        }

        DB::beginTransaction();
        try {
            $message = Support::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $data['message'] ?? null,
                'parent_id' => $parent ? $parent->id : null,
                'file' => $filePath,
                'file_info' => [
                    'ext' => $fileExtension,
                    'size' => $fileSize,
                ],
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $message;
    }

    protected function uploadFile(UploadedFile $file): string
    {
        $filename = $this->generatedFilename($file);
        $path = 'uploads/'.now()->format('y/m');

        return $file->storeAs($path, $filename, config('filesystems.default'));
    }
}
