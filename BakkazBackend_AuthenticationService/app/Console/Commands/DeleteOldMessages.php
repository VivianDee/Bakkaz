<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;

class DeleteOldMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete messages that are at least 1 day old';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Delete messages older than 1 day
        $deletedCount = Message::where('created_at', '<', now()->subHours(24))->delete();

        // Check if there were any messages deleted
        if ($deletedCount === 0) {
            $this->info('No messages to delete.');
        } else {
            $this->info("Deleted {$deletedCount} messages.");
        }
    }
}
