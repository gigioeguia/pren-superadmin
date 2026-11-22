<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasUploader
{
    /**
     * Upload a single file with an auto-generated filename.
     */
    private function upload(Request $request, string $input, ?string $oldFile = null, ?string $disk = null): string
    {
        /** @var UploadedFile $file */
        $file = $request->file($input);

        $filename = $this->generatedFilename($file);
        $path = 'uploads/'.now()->format('y/m');
        $filePath = $file->storeAs($path, $filename, $disk ?? config('filesystems.default'));

        $this->deleteOldFile($oldFile, $disk);

        return $filePath;
    }

    /**
     * Upload a single UploadedFile with an auto-generated filename, for
     * services that receive the file instead of the whole Request.
     */
    private function uploadFile(UploadedFile $file, ?string $oldFile = null, ?string $disk = null): string
    {
        $filename = $this->generatedFilename($file);
        $path = 'uploads/'.now()->format('y/m');
        $filePath = $file->storeAs($path, $filename, $disk ?? config('filesystems.default'));

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
            $filename = $this->generatedFilename($file);
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
     * Upload multiple files from a plain array and delete old files if provided.
     */
    private function multipleUploadArray(array $files, array $oldFiles = [], ?string $disk = null): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            /** @var UploadedFile $file */
            $filename = $this->generatedFilename($file);
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

    /**
     * Build a unique, normalized filename independent of the client input.
     */
    private function generatedFilename(UploadedFile $file): string
    {
        $extension = $this->normalizeExtension($file->getClientOriginalExtension());

        return now()->format('YmdHis').'_'.uniqid().'.'.$extension;
    }

    /**
     * Normalize a client-provided extension: lowercase and strip non-alphanumeric chars.
     */
    private function normalizeExtension(string $extension): string
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9]/', '', $extension));

        return $normalized !== '' ? $normalized : 'bin';
    }
}
