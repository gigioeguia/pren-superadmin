<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait HasUploader
{
    /**
     * Upload a single file with auto-generated filename.
     *
     * @param  Request  $request
     * @param  string   $input
     * @param  string|null  $oldFile
     * @param  string|null  $disk
     * @return string
     */
    private function upload(Request $request, string $input, ?string $oldFile = null, ?string $disk = null): string
    {
        /** @var UploadedFile $file */
        $file = $request->file($input);

        $filename = now()->timestamp . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/' . now()->format('y/m') . '-' . rand(1, 1000);
        $filePath = $file->storeAs($path, $filename, $disk ?? config('filesystems.default'));

        $this->deleteOldFile($oldFile, $disk);

        return $filePath;
    }

    /**
     * Upload a single file preserving its original filename.
     *
     * @param  Request  $request
     * @param  string   $input
     * @param  string|null  $oldFile
     * @param  string|null  $disk
     * @return string
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
     *
     * @param  Request  $request
     * @param  string   $input
     * @param  array    $oldFiles
     * @param  string|null  $disk
     * @return array
     */
    private function multipleUpload(Request $request, string $input, array $oldFiles = [], ?string $disk = null): array
    {
        $uploadedFiles = [];

        /** @var UploadedFile $file */
        foreach ($request->file($input) as $file) {
            $filename = now()->timestamp . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/' . now()->format('y/m');
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
            /** @var \Illuminate\Http\UploadedFile $file */
            $filename = now()->timestamp . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/' . now()->format('y/m');
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
     *
     * @param  string|null  $oldFile
     * @param  string|null  $disk
     * @return void
     */
    private function deleteOldFile(?string $oldFile, ?string $disk = null): void
    {
        if ($oldFile && Storage::disk($disk ?? config('filesystems.default'))->exists($oldFile)) {
            Storage::disk($disk ?? config('filesystems.default'))->delete($oldFile);
        }
    }
}
