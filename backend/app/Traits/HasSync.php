<?php

namespace App\Traits;

trait HasSync
{
    // Sync status constants
    const SYNC_STATUS_PENDING = 'pending';
    const SYNC_STATUS_SYNCED = 'synced';
    const SYNC_STATUS_FAILED = 'failed';

    /**
     * Boot the trait
     */
    protected static function bootHasSync()
    {
        static::creating(function ($model) {
            if (!isset($model->sync_status)) {
                $model->sync_status = self::SYNC_STATUS_PENDING;
            }
        });
    }

    /**
     * Check if the model is synced
     */
    public function isSynced()
    {
        return $this->sync_status === self::SYNC_STATUS_SYNCED;
    }

    /**
     * Check if the model is pending sync
     */
    public function isSyncPending()
    {
        return $this->sync_status === self::SYNC_STATUS_PENDING;
    }

    /**
     * Check if the model sync failed
     */
    public function isSyncFailed()
    {
        return $this->sync_status === self::SYNC_STATUS_FAILED;
    }

    /**
     * Mark the model as synced
     */
    public function markAsSynced()
    {
        $this->sync_status = self::SYNC_STATUS_SYNCED;
        $this->last_sync_at = now();
        $this->save();
    }

    /**
     * Mark the model as pending sync
     */
    public function markAsPending()
    {
        $this->sync_status = self::SYNC_STATUS_PENDING;
        $this->save();
    }

    /**
     * Mark the model as failed sync
     */
    public function markAsFailed()
    {
        $this->sync_status = self::SYNC_STATUS_FAILED;
        $this->save();
    }

    /**
     * Scope a query to only include pending sync records
     */
    public function scopePendingSync($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_PENDING);
    }

    /**
     * Scope a query to only include synced records
     */
    public function scopeSynced($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_SYNCED);
    }

    /**
     * Scope a query to only include failed sync records
     */
    public function scopeFailedSync($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_FAILED);
    }

    /**
     * Scope a query to only include records for a specific device
     */
    public function scopeForDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }
} 