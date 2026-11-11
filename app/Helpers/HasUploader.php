<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasUploader
{
    /**
     * Upload a single file with auto-generated filename.
     */
    private function upload(Request $request, string $input, ?string $oldFile = null, ?string $disk = null): string
    {
        /** @var UploadedFile $file */
        $file = $request->file($input);

        $filename = now()->timestamp.'.'.$file->getClientOriginalExtension();
        $path = 'uploads/'.now()->format('y/m').'-'.rand(1, 1000);
        $filePath = $file->storeAs($path, $filename, $disk ?? config('filesystems.default'));

        $this->deleteOldFile($oldFile, $disk);

        return $filePath;
    }

    /**
     * Upload a single file preserving its original filename.
     */
    private function uploadWithFileName(Request $request, string $input, ?string $oldFile = null, ?string $disk = null): string
    {
        /** @var UploadedFile $file */
        $file = $request->file($input);

        $path = 'files';
        $filePath = $file->storeAs($path, $file->getClientOriginalName(), $disk ?? config('filesystems.default'));

        $this->deleteOldFile($oldFile, $disk);

        return $filePath;
    }

    /**
     * Upload multiple files and delete old files if provided.
     */
    private function multipleUpload(Request $request, string $input, array $oldFiles = [], ?string $disk = null): array
    {
        $uploadedFiles = [];

        /** @var UploadedFile $file */
        foreach ($request->file($input) as $file) {
            $filename = now()->timestamp.'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = 'uploads/'.now()->format('y/m');
            $filePath = $file->storeAs($path, $filename, $disk ?? config('filesystems.default'));

            $uploadedFiles[] = $filePath;
        }

        foreach ($oldFiles as $oldFile) {
            $this->deleteOldFile($oldFile, $disk);
        }

        return $uploadedFiles;
    }

    private function multipleUploadArray(array $files, array $oldFiles = [], ?string $disk = null): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            /** @var UploadedFile $file */
            $filename = now()->timestamp.'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = 'uploads/'.now()->format('y/m');
            $filePath = $file->storeAs($path, $filename, $disk ?? config('filesystems.default'));
            $uploadedFiles[] = $filePath;
        }

        foreach ($oldFiles as $oldFile) {
            $this->deleteOldFile($oldFile, $disk);
        }

        return $uploadedFiles;
    }

    /**
     * Delete old file from storage if it exists.
     */
    private function deleteOldFile(?string $oldFile, ?string $disk = null): void
    {
        if ($oldFile && Storage::disk($disk ?? config('filesystems.default'))->exists($oldFile)) {
            Storage::disk($disk ?? config('filesystems.default'))->delete($oldFile);
        }
    }
}
