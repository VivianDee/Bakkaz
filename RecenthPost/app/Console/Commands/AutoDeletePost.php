<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoDeletePost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-delete-post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Posts older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('posts')->where('created_at', '<', now()->subHours(24))->update([
            'deleted_at' => now()
        ]);
    }
}
