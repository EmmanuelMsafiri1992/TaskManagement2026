<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAdvancePayments extends Command
{
    protected $signature = 'migrate:advance-payments';
    protected $description = 'Migrate old advance_payments data to advance_requests table';

    public function handle()
    {
        $oldRecords = DB::table('advance_payments')->get();
        
        if ($oldRecords->isEmpty()) {
            $this->info('No records found in advance_payments table.');
            return 0;
        }

        $this->info("Found {$oldRecords->count()} records to migrate...");
        $migrated = 0;

        foreach ($oldRecords as $old) {
            // Check if already migrated (by user_id, amount, and created_at)
            $exists = DB::table('advance_requests')
                ->where('user_id', $old->user_id)
                ->where('amount', $old->amount)
                ->where('created_at', $old->created_at)
                ->exists();

            if ($exists) {
                $this->line("Skipping record ID {$old->id} - already migrated");
                continue;
            }

            // Map old status to new status
            $status = $old->status;
            if ($status === 'paid') {
                $status = 'deducted';
            }

            // Calculate amount_deducted based on status
            $amountDeducted = ($status === 'deducted') ? $old->amount : 0;
            $remainingBalance = $old->amount - $amountDeducted;

            DB::table('advance_requests')->insert([
                'user_id' => $old->user_id,
                'amount' => $old->amount,
                'currency' => $old->currency ?? 'MWK',
                'reason' => $old->reason,
                'status' => $status,
                'approved_by' => $old->approved_by,
                'approved_at' => $old->approved_at,
                'admin_notes' => $old->admin_notes,
                'amount_deducted' => $amountDeducted,
                'remaining_balance' => $remainingBalance,
                'payroll_id' => $old->deducted_from_payroll_id,
                'expected_deduction_date' => $old->payment_date,
                'created_at' => $old->created_at,
                'updated_at' => $old->updated_at,
            ]);
            $migrated++;
            $this->line("Migrated record ID {$old->id}");
        }

        $this->info("Migration complete! Migrated {$migrated} records.");
        $this->info("Total records in advance_requests: " . DB::table('advance_requests')->count());

        return 0;
    }
}
