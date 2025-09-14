<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait AuditableTrait
{
    /**
     * Log an audit event for the model.
     */
    public function logAuditEvent(string $event, ?string $description = null, ?array $meta = null): AuditLog
    {
        // Get user ID from either regular auth or admin auth
        $userId = Auth::id();
        
        // For admin operations, set user_id to null to avoid foreign key issues
        if (Auth::guard('admin')->check()) {
            $userId = null; // Admin operations don't reference users table
        }

        // For admin operations, set cabinet_id to null to avoid foreign key issues
        $cabinetId = null;
        if (!Auth::guard('admin')->check()) {
            $cabinetId = $this->getCabinetId();
        }

        $data = [
            'user_id' => $userId,
            'cabinet_id' => $cabinetId,
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'event' => $event,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $meta,
        ];

        return AuditLog::create($data);
    }

    /**
     * Get the cabinet ID for audit logging.
     * Override this method in models that have cabinet relationships.
     */
    protected function getCabinetId(): ?int
    {
        // Check if model has cabinet_id attribute
        if (isset($this->cabinet_id)) {
            return $this->cabinet_id;
        }

        // Check if model has cabinet relationship
        if (method_exists($this, 'cabinet') && $this->cabinet) {
            return $this->cabinet->id;
        }

        // Check if model has cabinets relationship (many-to-many)
        if (method_exists($this, 'cabinets') && $this->cabinets()->exists()) {
            return $this->cabinets()->first()?->id;
        }

        return null;
    }

    /**
     * Boot the auditable trait.
     */
    protected static function bootAuditableTrait(): void
    {
        // Log when model is created
        static::created(function (Model $model) {
            $model->logAuditEvent('created', 'Model was created');
        });

        // Log when model is updated
        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $description = 'Model was updated';
            
            if (!empty($changes)) {
                $description .= ': ' . implode(', ', array_keys($changes));
            }

            $model->logAuditEvent('updated', $description, $changes);
        });

        // Log when model is deleted
        static::deleted(function (Model $model) {
            $model->logAuditEvent('deleted', 'Model was deleted');
        });
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'subject', 'subject_type', 'subject_id');
    }

    /**
     * Log a custom event without automatic booting.
     */
    public function logCustomEvent(string $event, ?string $description = null, ?array $meta = null): AuditLog
    {
        return $this->logAuditEvent($event, $description, $meta);
    }
}
