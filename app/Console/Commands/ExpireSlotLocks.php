<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExpireSlotLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expire:slot-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired time slot locks older than 10 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Illuminate\Support\Facades\DB::statement("
            UPDATE time_slots
            SET status = 'available',
            slot_date = NULL,
            locked_by = NULL,
            lock_expires_at = NULL
            WHERE status = 'locked'
            AND lock_expires_at < NOW()
        ");

        $this->info('Expired slot locks released successfully.');
    }
}
