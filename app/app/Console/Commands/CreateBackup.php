<?php

namespace App\Console\Commands;

use App\BackupManager;
use App\Models\AuditLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backup {--keep=14 : How many backups to keep}')]
#[Description('Back up the database and uploaded files, keeping the newest archives')]
class CreateBackup extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BackupManager $backups): int
    {
        if (! $backups->isSupported()) {
            $this->error('Backups from the platform work with SQLite only.');

            return self::FAILURE;
        }

        $name = $backups->create();
        $removed = $backups->prune(max(1, (int) $this->option('keep')));

        AuditLog::record('backup.created', properties: ['file' => $name, 'via' => 'schedule']);

        $this->info("Created {$name}. Removed {$removed} old backup(s).");

        return self::SUCCESS;
    }
}
