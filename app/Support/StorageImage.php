<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorageImage
{
    public const DISK = 'public';

    public static function normalize(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            $parsedPath = parse_url($path, PHP_URL_PATH);

            if ($parsedPath) {
                $parsedPath = str_replace('\\', '/', $parsedPath);
                $parsedPath = ltrim($parsedPath, '/');

                for ($i = 0; $i < 5; $i++) {
                    $next = preg_replace('#^(public/|storage/)#', '', $parsedPath);
                    if ($next === $parsedPath) {
                        break;
                    }
                    $parsedPath = ltrim($next, '/');
                }

                return $parsedPath ?: null;
            }

            return $path;
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        for ($i = 0; $i < 5; $i++) {
            $next = preg_replace('#^(public/|storage/)#', '', $path);
            if ($next === $path) {
                break;
            }
            $path = ltrim($next, '/');
        }

        return $path ?: null;
    }

    public static function storeUploadedFile(UploadedFile $file, string $directory): string
    {
        self::ensureDirectory($directory);

        $path = $file->store($directory, self::DISK);

        if (! $path || ! Storage::disk(self::DISK)->exists($path)) {
            Log::error('StorageImage: upload failed', [
                'directory' => $directory,
                'original_name' => $file->getClientOriginalName(),
                'disk_root' => Storage::disk(self::DISK)->path(''),
            ]);

            throw new \RuntimeException('Failed to store uploaded image.');
        }

        Log::info('StorageImage: stored', [
            'path' => $path,
            'absolute' => Storage::disk(self::DISK)->path($path),
        ]);

        return $path;
    }

    public static function exists(?string $path): bool
    {
        $normalized = self::normalize($path);

        if (! $normalized || str_starts_with($normalized, 'http')) {
            return false;
        }

        return self::ensureOnDisk($normalized);
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            $parsedPath = parse_url($path, PHP_URL_PATH);

            if ($parsedPath && (str_starts_with($parsedPath, '/storage/') || str_contains($parsedPath, '/storage/'))) {
                $normalized = self::normalize($path);

                if ($normalized && self::ensureOnDisk($normalized)) {
                    return self::publicUrl($normalized);
                }

                self::logMissing($path, $normalized);

                return null;
            }

            return $path;
        }

        $normalized = self::normalize($path);

        if (! $normalized) {
            return null;
        }

        if (! self::ensureOnDisk($normalized)) {
            self::logMissing($path, $normalized);

            return null;
        }

        return self::publicUrl($normalized);
    }

    public static function urlOrFallback(?string $path, ?string $fallback = null): ?string
    {
        return self::url($path) ?? $fallback;
    }

    public static function delete(?string $path): void
    {
        $normalized = self::normalize($path);

        if ($normalized && Storage::disk(self::DISK)->exists($normalized)) {
            Storage::disk(self::DISK)->delete($normalized);
        }
    }

    /**
     * Ensure storage/app/public subdirectories exist before upload.
     */
    private static function ensureDirectory(string $directory): void
    {
        $diskPath = Storage::disk(self::DISK)->path($directory);

        if (! is_dir($diskPath)) {
            File::makeDirectory($diskPath, 0755, true);
        }
    }

    /**
     * Resolve a file on the public disk, migrating legacy public/* copies when found.
     */
    private static function ensureOnDisk(string $normalized): bool
    {
        if (Storage::disk(self::DISK)->exists($normalized)) {
            return true;
        }

        $legacySource = self::findLegacySource($normalized);

        if ($legacySource === null) {
            return false;
        }

        $contents = File::get($legacySource);
        Storage::disk(self::DISK)->put($normalized, $contents);

        Log::info('StorageImage: migrated legacy file to public disk', [
            'from' => $legacySource,
            'to' => $normalized,
            'disk_path' => Storage::disk(self::DISK)->path($normalized),
        ]);

        return Storage::disk(self::DISK)->exists($normalized);
    }

    private static function findLegacySource(string $normalized): ?string
    {
        $candidates = [
            public_path(str_replace('/', DIRECTORY_SEPARATOR, $normalized)),
            public_path('storage'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalized)),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * URL path served via public/storage symlink (or storage.serve route).
     */
    private static function publicUrl(string $normalized): string
    {
        $generated = Storage::disk(self::DISK)->url($normalized);
        $path = parse_url($generated, PHP_URL_PATH);

        return $path ?: '/storage/'.$normalized;
    }

    private static function logMissing(?string $dbPath, ?string $normalized): void
    {
        $legacyCandidates = [];

        if ($normalized) {
            $legacyCandidates = array_filter([
                public_path(str_replace('/', DIRECTORY_SEPARATOR, $normalized)),
                public_path('storage'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalized)),
            ], 'is_file');
        }

        Log::warning('StorageImage: file not found on public disk', [
            'db_path' => $dbPath,
            'normalized' => $normalized,
            'disk_root' => Storage::disk(self::DISK)->path(''),
            'expected_file' => $normalized
                ? Storage::disk(self::DISK)->path($normalized)
                : null,
            'legacy_candidates_checked' => $legacyCandidates,
            'hint' => empty($legacyCandidates)
                ? 'Run: php artisan images:migrate-legacy && php artisan storage:ensure-link'
                : 'Legacy file exists but could not be migrated automatically',
        ]);
    }
}
