<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ModelChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'model_change_history';

    protected $fillable = [
        'model_type',
        'model_id',
        'field_name',
        'old_value',
        'new_value',
        'change_type',
        'changed_at',
        'user_id',
        'source'
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who made the change
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the actual model instance that was changed
     */
    public function getModelInstance()
    {
        $modelClass = "App\\Models\\{$this->model_type}";
        
        if (class_exists($modelClass)) {
            return $modelClass::find($this->model_id);
        }
        
        return null;
    }

    /**
     * Get changes for a specific model
     */
    public static function getChangesForModel(string $modelType, int $modelId, array $fields = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::where('model_type', $modelType)
                    ->where('model_id', $modelId)
                    ->orderBy('changed_at', 'desc');

        if ($fields) {
            $query->whereIn('field_name', $fields);
        }

        return $query->get();
    }

    /**
     * Get the latest change for a specific field
     */
    public static function getLatestFieldChange(string $modelType, int $modelId, string $fieldName): ?self
    {
        return self::where('model_type', $modelType)
                  ->where('model_id', $modelId)
                  ->where('field_name', $fieldName)
                  ->orderBy('changed_at', 'desc')
                  ->first();
    }

    /**
     * Get all fields that have changed since a specific date
     */
    public static function getChangedFieldsSince(string $modelType, int $modelId, Carbon $since): array
    {
        return self::where('model_type', $modelType)
                  ->where('model_id', $modelId)
                  ->where('changed_at', '>', $since)
                  ->pluck('field_name')
                  ->unique()
                  ->toArray();
    }

    /**
     * Check if a field has changed since a specific date
     */
    public static function hasFieldChangedSince(string $modelType, int $modelId, string $fieldName, Carbon $since): bool
    {
        return self::where('model_type', $modelType)
                  ->where('model_id', $modelId)
                  ->where('field_name', $fieldName)
                  ->where('changed_at', '>', $since)
                  ->exists();
    }

    /**
     * Get models that have any changes since a specific date
     */
    public static function getModelsWithChangesSince(string $modelType, Carbon $since): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('model_type', $modelType)
                  ->where('changed_at', '>', $since)
                  ->select('model_id')
                  ->distinct()
                  ->get();
    }

    /**
     * Create a change record
     */
    public static function recordChange(string $modelType, int $modelId, string $fieldName, $oldValue, $newValue, string $changeType = 'update', int $userId = null, string $source = 'web'): self
    {
        // Convert arrays/objects to JSON for storage
        $oldValue = is_array($oldValue) || is_object($oldValue) ? json_encode($oldValue) : $oldValue;
        $newValue = is_array($newValue) || is_object($newValue) ? json_encode($newValue) : $newValue;

        return self::create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'change_type' => $changeType,
            'changed_at' => now(),
            'user_id' => $userId,
            'source' => $source
        ]);
    }

    /**
     * Get decoded old value
     */
    public function getDecodedOldValue()
    {
        if (is_null($this->old_value)) {
            return null;
        }

        // Try to decode JSON, return original if it fails
        $decoded = json_decode($this->old_value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->old_value;
    }

    /**
     * Get decoded new value
     */
    public function getDecodedNewValue()
    {
        if (is_null($this->new_value)) {
            return null;
        }

        // Try to decode JSON, return original if it fails
        $decoded = json_decode($this->new_value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->new_value;
    }

    /**
     * Check if values are actually different
     */
    public function valuesAreDifferent(): bool
    {
        return $this->getDecodedOldValue() !== $this->getDecodedNewValue();
    }
}
