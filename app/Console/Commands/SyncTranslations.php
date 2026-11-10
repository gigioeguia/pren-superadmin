<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncTranslations extends Command
{
    protected $signature = 'lang:sync';
    protected $description = 'Sync language JSON files with en.json (remove extra, add missing, keep translations, append new keys at bottom)';

    public function handle()
    {
        $path = lang_path();
        $baseFile = $path . '/en.json';

        if (!File::exists($baseFile)) {
            $this->error("en.json not found!");
            return 1;
        }

        $en = json_decode(File::get($baseFile), true);

        if (!is_array($en)) {
            $this->error("Invalid en.json format");
            return 1;
        }

        $ignoreFiles = [
            'countrylist.json',
            'phone_code.json',
            'langlist.json',
        ];

        $files = File::files($path);

        foreach ($files as $file) {

            $fileName = $file->getFilename();

            if ($fileName === 'en.json' || in_array($fileName, $ignoreFiles)) {
                continue;
            }

            $this->info("\nSyncing: {$fileName}");

            $data = json_decode(File::get($file->getPathname()), true);

            if (!is_array($data)) {
                $data = [];
            }

            $existing = [];
            $newKeys = [];

            // 1. Keep existing keys (only valid ones from en.json)
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $en)) {
                    $existing[$key] = $value;
                }
            }

            // 2. Add missing keys at bottom (use en value as fallback)
            foreach ($en as $key => $enValue) {
                if (!array_key_exists($key, $data)) {
                    $newKeys[$key] = $enValue;
                }
            }

            // 3. Merge (existing first, new at bottom)
            $final = $existing + $newKeys;

            // 4. Save file
            File::put(
                $file->getPathname(),
                json_encode($final, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $this->info("  ✅ Synced successfully");
        }

        $this->info("\n🔥 All language files synced with en.json");

        return 0;
    }
}
