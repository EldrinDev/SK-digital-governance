<?php

namespace App;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Backs up and restores the database and uploaded files as one zip archive.
 *
 * Each archive holds "database.sqlite" (a consistent copy taken with VACUUM INTO)
 * and every uploaded file under "files/". Works with SQLite, the engine the
 * platform uses; other engines need their own database tools.
 */
class BackupManager
{
    /**
     * Folder (inside the files root) where archives are kept. It is never backed up or overwritten.
     */
    public const string FOLDER = 'backups';

    public function __construct(
        private string $connection,
        private string $filesRoot,
    ) {}

    /**
     * Build a manager for the application's default database and private storage.
     */
    public static function forApplication(): self
    {
        return new self(config('database.default'), storage_path('app/private'));
    }

    /**
     * Determine whether the database engine can be backed up from the platform.
     */
    public function isSupported(): bool
    {
        return DB::connection($this->connection)->getDriverName() === 'sqlite';
    }

    /**
     * Create a new archive and return its file name.
     */
    public function create(): string
    {
        $this->ensureSupported();

        $directory = $this->directory();
        File::ensureDirectoryExists($directory);

        $name = 'backup-'.now()->format('Y-m-d-His').'-'.Str::lower(Str::random(4)).'.zip';
        $databaseCopy = $directory.DIRECTORY_SEPARATOR.Str::random(16).'.sqlite';

        DB::connection($this->connection)->statement('VACUUM INTO ?', [$databaseCopy]);

        $zip = new ZipArchive;

        if ($zip->open($directory.DIRECTORY_SEPARATOR.$name, ZipArchive::CREATE) !== true) {
            File::delete($databaseCopy);

            throw new RuntimeException('The backup archive could not be created.');
        }

        $zip->addFile($databaseCopy, 'database.sqlite');

        foreach ($this->uploadedFiles() as $path) {
            $zip->addFile($path, 'files/'.$this->relative($path));
        }

        $zip->close();
        File::delete($databaseCopy);

        return $name;
    }

    /**
     * List archives, newest first.
     *
     * @return Collection<int, array{name: string, size: int, created_at: Carbon}>
     */
    public function all(): Collection
    {
        if (! File::isDirectory($this->directory())) {
            return collect();
        }

        return collect(File::files($this->directory()))
            ->filter(fn ($file): bool => $file->getExtension() === 'zip')
            ->map(fn ($file): array => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'created_at' => Carbon::createFromTimestamp($file->getMTime()),
            ])
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * Get the full path of an archive, refusing anything that isn't one of ours.
     */
    public function path(string $name): string
    {
        if (preg_match('/^backup-[0-9-]+-[a-z0-9]{4}\.zip$/', $name) !== 1) {
            throw new RuntimeException('Unknown backup.');
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.$name;

        if (! File::exists($path)) {
            throw new RuntimeException('Unknown backup.');
        }

        return $path;
    }

    /**
     * Delete an archive.
     */
    public function delete(string $name): void
    {
        File::delete($this->path($name));
    }

    /**
     * Keep only the newest archives.
     */
    public function prune(int $keep): int
    {
        $old = $this->all()->slice($keep);
        $old->each(fn (array $backup) => File::delete($this->directory().DIRECTORY_SEPARATOR.$backup['name']));

        return $old->count();
    }

    /**
     * Replace the database and uploaded files with an archive's contents.
     * A safety backup of the current state is taken first.
     *
     * @return string The name of the safety backup.
     */
    public function restore(string $name): string
    {
        $this->ensureSupported();

        $archive = $this->path($name);
        $databasePath = DB::connection($this->connection)->getConfig('database');

        if ($databasePath === ':memory:' || ! File::exists($databasePath)) {
            throw new RuntimeException('The database file could not be found, so it can’t be replaced.');
        }

        $zip = new ZipArchive;

        if ($zip->open($archive) !== true || $zip->locateName('database.sqlite') === false) {
            throw new RuntimeException('This file isn’t a valid backup.');
        }

        $safety = $this->create();

        $workDir = storage_path('framework/restore-'.Str::random(8));
        File::ensureDirectoryExists($workDir);

        try {
            $entries = [];

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->getNameIndex($index);

                if ($entry === 'database.sqlite' || (str_starts_with($entry, 'files/') && ! str_ends_with($entry, '/') && ! str_contains($entry, '..') && ! str_contains($entry, '\\'))) {
                    $entries[] = $entry;
                }
            }

            $zip->extractTo($workDir, $entries);
            $zip->close();

            DB::purge($this->connection);
            File::copy($workDir.DIRECTORY_SEPARATOR.'database.sqlite', $databasePath);
            DB::reconnect($this->connection);
            $this->signEveryoneOut();

            foreach ($this->uploadedFiles() as $path) {
                File::delete($path);
            }

            if (File::isDirectory($workDir.DIRECTORY_SEPARATOR.'files')) {
                foreach (File::allFiles($workDir.DIRECTORY_SEPARATOR.'files') as $file) {
                    $target = $this->filesRoot.DIRECTORY_SEPARATOR.$file->getRelativePathname();
                    File::ensureDirectoryExists(dirname($target));
                    File::copy($file->getPathname(), $target);
                }
            }
        } finally {
            File::deleteDirectory($workDir);
        }

        return $safety;
    }

    /**
     * Drop the sessions and remember-me tokens that came back with the restored database,
     * so nobody is signed in by state from the past (e.g. an account deactivated since).
     */
    private function signEveryoneOut(): void
    {
        $schema = DB::connection($this->connection)->getSchemaBuilder();
        $sessions = config('session.table', 'sessions');

        if ($schema->hasTable($sessions)) {
            DB::connection($this->connection)->table($sessions)->delete();
        }

        if ($schema->hasColumn('users', 'remember_token')) {
            DB::connection($this->connection)->table('users')->update(['remember_token' => null]);
        }
    }

    /**
     * Uploaded files, skipping the archive folder itself.
     *
     * @return list<string>
     */
    private function uploadedFiles(): array
    {
        if (! File::isDirectory($this->filesRoot)) {
            return [];
        }

        return collect(File::allFiles($this->filesRoot))
            ->map(fn ($file): string => $file->getPathname())
            ->reject(fn (string $path): bool => str_starts_with($this->relative($path), self::FOLDER.'/'))
            ->values()
            ->all();
    }

    private function relative(string $path): string
    {
        return str_replace('\\', '/', ltrim(Str::after($path, $this->filesRoot), '\\/'));
    }

    private function directory(): string
    {
        return $this->filesRoot.DIRECTORY_SEPARATOR.self::FOLDER;
    }

    private function ensureSupported(): void
    {
        if (! $this->isSupported()) {
            throw new RuntimeException('Backups from the platform work with SQLite only. Use your database server’s own backup tools.');
        }
    }
}
