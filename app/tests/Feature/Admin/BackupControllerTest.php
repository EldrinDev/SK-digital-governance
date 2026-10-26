<?php

namespace Tests\Feature\Admin;

use App\BackupManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sk-backup-ui-'.uniqid();
        File::ensureDirectoryExists($this->root);
        touch($this->root.'/db.sqlite');

        // The suite's own database runs inside a transaction, where SQLite can't VACUUM,
        // so the screens are exercised against a separate throwaway database file.
        config(['database.connections.backup_ui' => ['driver' => 'sqlite', 'database' => $this->root.'/db.sqlite', 'prefix' => '']]);
        $this->app->bind(BackupManager::class, fn () => new BackupManager('backup_ui', $this->root.'/files'));
    }

    protected function tearDown(): void
    {
        DB::purge('backup_ui');
        File::deleteDirectory($this->root);

        parent::tearDown();
    }

    public function test_admin_creates_downloads_and_deletes_a_backup(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->post(route('admin.backups.store'))->assertSessionHas('status');
        $name = app(BackupManager::class)->all()->sole()['name'];
        $this->assertDatabaseHas('audit_logs', ['action' => 'backup.created']);

        $this->actingAs($admin)->get(route('admin.backups.index'))->assertSeeText($name);
        $this->actingAs($admin)->get(route('admin.backups.show', $name))->assertOk()->assertDownload($name);

        $this->actingAs($admin)->delete(route('admin.backups.destroy', $name));
        $this->assertCount(0, app(BackupManager::class)->all());
    }

    public function test_restore_needs_typed_confirmation(): void
    {
        $admin = User::factory()->administrator()->create();
        $name = app(BackupManager::class)->create();

        $this->actingAs($admin)
            ->post(route('admin.backups.restore', $name), ['confirmation' => 'restore'])
            ->assertSessionHasErrors(['confirmation' => 'Type RESTORE in capital letters to confirm.']);
    }

    public function test_unknown_backup_name_is_not_found(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.backups.show', '..%2F..%2F.env'))
            ->assertNotFound();
    }

    public function test_federation_user_cannot_manage_backups(): void
    {
        $this->actingAs(User::factory()->federation()->create())->get(route('admin.backups.index'))->assertForbidden();
        $this->actingAs(User::factory()->federation()->create())->post(route('admin.backups.store'))->assertForbidden();
    }
}
