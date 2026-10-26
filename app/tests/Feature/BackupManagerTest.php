<?php

namespace Tests\Feature;

use App\BackupManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

/**
 * Exercises a real backup and restore against a throwaway SQLite file,
 * since the test suite's own database lives in memory.
 */
class BackupManagerTest extends TestCase
{
    private string $directory;

    private BackupManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sk-backup-test-'.uniqid();
        File::ensureDirectoryExists($this->directory.'/files/evidence');
        touch($this->directory.'/db.sqlite');

        config(['database.connections.backup_test' => [
            'driver' => 'sqlite',
            'database' => $this->directory.'/db.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);

        DB::connection('backup_test')->statement('create table notes (id integer primary key, body text)');
        DB::connection('backup_test')->table('notes')->insert(['body' => 'original']);
        DB::connection('backup_test')->statement('create table sessions (id text primary key, user_id integer)');
        DB::connection('backup_test')->statement('create table users (id integer primary key, remember_token text)');
        DB::connection('backup_test')->table('sessions')->insert(['id' => 'old-session', 'user_id' => 1]);
        DB::connection('backup_test')->table('users')->insert(['id' => 1, 'remember_token' => 'old-token']);
        File::put($this->directory.'/files/evidence/photo.jpg', 'original photo');

        $this->manager = new BackupManager('backup_test', $this->directory.'/files');
    }

    protected function tearDown(): void
    {
        DB::purge('backup_test');
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_backup_then_restore_brings_back_data_and_files(): void
    {
        $name = $this->manager->create();

        DB::connection('backup_test')->table('notes')->update(['body' => 'changed']);
        File::put($this->directory.'/files/evidence/photo.jpg', 'changed photo');
        File::put($this->directory.'/files/evidence/new.jpg', 'added later');

        $safety = $this->manager->restore($name);

        $this->assertSame('original', DB::connection('backup_test')->table('notes')->value('body'));
        $this->assertSame('original photo', File::get($this->directory.'/files/evidence/photo.jpg'));
        $this->assertFileDoesNotExist($this->directory.'/files/evidence/new.jpg');
        $this->assertContains($safety, $this->manager->all()->pluck('name'));
    }

    public function test_restore_does_not_bring_back_sessions_or_remember_tokens(): void
    {
        $name = $this->manager->create();

        $this->manager->restore($name);

        $this->assertSame(0, DB::connection('backup_test')->table('sessions')->count());
        $this->assertNull(DB::connection('backup_test')->table('users')->value('remember_token'));
    }

    public function test_archive_holds_database_and_files_but_not_other_backups(): void
    {
        $this->manager->create();
        $second = $this->manager->create();

        $zip = new ZipArchive;
        $zip->open($this->manager->path($second));
        $names = collect(range(0, $zip->numFiles - 1))->map(fn ($index) => $zip->getNameIndex($index))->all();
        $zip->close();

        $this->assertEqualsCanonicalizing(['database.sqlite', 'files/evidence/photo.jpg'], $names);
    }

    public function test_prune_keeps_the_newest(): void
    {
        foreach (range(1, 3) as $i) {
            $this->manager->create();
            sleep(1);
        }

        $this->assertSame(2, $this->manager->prune(1));
        $this->assertCount(1, $this->manager->all());
    }

    public function test_rejects_names_outside_the_backup_folder(): void
    {
        $this->expectException(RuntimeException::class);

        $this->manager->path('../db.sqlite');
    }
}
