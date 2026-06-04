<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSqlColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sql-columns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = 'arsip_adjust_items';
        $cols = \DB::select(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = DATABASE() ORDER BY ORDINAL_POSITION",
            [$table]
        );

        foreach ($cols as $c) {
            $this->line($c->COLUMN_NAME);
        }
    }

}
