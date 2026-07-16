<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SePayService;

class SyncSePayTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sepay:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync transactions from SePay API to local database';

    /**
     * Execute the console command.
     */
    public function handle(SePayService $sePayService)
    {
        $this->info('Bắt đầu đồng bộ giao dịch từ SePay API...');

        try {
            $count = $sePayService->syncTransactions();
            
            if ($count > 0) {
                $this->info("Đồng bộ thành công {$count} giao dịch mới.");
            } else {
                $this->info("Không có giao dịch nào mới để đồng bộ.");
            }
        } catch (\Exception $e) {
            $this->error('Lỗi khi đồng bộ: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
