<?php

namespace TronderData\TdCostCalcultaror\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class CostItemLog extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cost_item_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the cost item that this log is for.
     */
    public function costItem(): BelongsTo
    {
        return $this->belongsTo(CostItem::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format the log entry for display.
     */
    public function formatMessage(): string
    {
        $userName = $this->user ? $this->user->name : __('td-cost-calcultaror::messages.unknown_user');
        $itemName = $this->costItem ? $this->costItem->name : __('td-cost-calcultaror::messages.deleted_item');
        
        switch ($this->action) {
            case 'create':
                return __('td-cost-calcultaror::messages.log_created', ['user' => $userName, 'item' => $itemName]);
                
            case 'update':
                $changes = $this->getChangesDescription();
                return __('td-cost-calcultaror::messages.log_updated', [
                    'user' => $userName, 
                    'item' => $itemName,
                    'changes' => $changes
                ]);
                
            case 'delete':
                return __('td-cost-calcultaror::messages.log_deleted', ['user' => $userName, 'item' => $itemName]);
                
            default:
                return __('td-cost-calcultaror::messages.log_modified', ['user' => $userName, 'item' => $itemName]);
        }
    }
    
    /**
     * Get a human-readable description of changes.
     */
    protected function getChangesDescription(): string
    {
        if (!$this->old_value || !$this->new_value) {
            return '';
        }
        
        $changes = [];
        
        foreach ($this->new_value as $key => $newValue) {
            if (!isset($this->old_value[$key]) || $this->old_value[$key] !== $newValue) {
                $oldValue = $this->old_value[$key] ?? __('td-cost-calcultaror::messages.not_set');
                $changes[] = __('td-cost-calcultaror::messages.field_changed', [
                    'field' => $key,
                    'old' => $oldValue,
                    'new' => $newValue
                ]);
            }
        }
        
        return implode(', ', $changes);
    }
    
    /**
     * Get the formatted old value.
     *
     * @return string
     */
    public function getFormattedOldValueAttribute(): string
    {
        if (!$this->old_value) {
            return '';
        }
        
        if (is_array($this->old_value)) {
            return json_encode($this->old_value, JSON_PRETTY_PRINT);
        }
        
        return (string)$this->old_value;
    }
    
    /**
     * Get the formatted new value.
     *
     * @return string
     */
    public function getFormattedNewValueAttribute(): string
    {
        if (!$this->new_value) {
            return '';
        }
        
        if (is_array($this->new_value)) {
            return json_encode($this->new_value, JSON_PRETTY_PRINT);
        }
        
        return (string)$this->new_value;
    }
}
