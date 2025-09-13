<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Repositories\AuditLogRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class LogAuditEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array $auditData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AuditLogRepository $auditLogRepository): void
    {
        try {
            $auditLogRepository->log($this->auditData);
        } catch (\Exception $e) {
            Log::error('Failed to log audit event', [
                'error' => $e->getMessage(),
                'audit_data' => $this->auditData,
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Audit log job failed permanently', [
            'error' => $exception->getMessage(),
            'audit_data' => $this->auditData,
        ]);
    }
}
