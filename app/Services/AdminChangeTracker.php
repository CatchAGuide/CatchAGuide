<?php

namespace App\Services;

use App\Models\Vacation;
use App\Models\ModelChangeHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminChangeTracker
{
    private array $translatableFields = [
        'title',
        'surroundings_description',
        'accommodation_description',
        'boat_description',
        'basic_fishing_description',
        'catering_info',
        'best_travel_times',
        'target_fish',
        'travel_options',
        'included_services',
        'additional_services',
        'amenities',
        'equipment'
    ];

    /**
     * Track changes to a vacation from admin interface
     */
    public function trackVacationChanges(Vacation $vacation, array $newData): void
    {
        try {
            $userId = Auth::id();
            $hasTranslatableChanges = false;
            
            foreach ($this->translatableFields as $field) {
                if (!isset($newData[$field])) {
                    continue;
                }
                
                $oldValue = $vacation->getOriginal($field);
                $newValue = $newData[$field];
                
                // Skip if values are the same
                if ($this->valuesAreEqual($oldValue, $newValue)) {
                    continue;
                }
                
                // Record the change
                ModelChangeHistory::recordChange(
                    'Vacation',
                    $vacation->id,
                    $field,
                    $oldValue,
                    $newValue,
                    'update',
                    $userId,
                    'admin'
                );
                
                $hasTranslatableChanges = true;
            }
            
            // Mark vacation as needing translation if there are translatable changes
            if ($hasTranslatableChanges) {
                $this->markVacationForTranslation($vacation);
                
                Log::info('Vacation marked for translation due to admin changes', [
                    'vacation_id' => $vacation->id,
                    'user_id' => $userId
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error tracking vacation changes', [
                'vacation_id' => $vacation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark a vacation as needing translation
     */
    private function markVacationForTranslation(Vacation $vacation): void
    {
        ModelChangeHistory::recordChange(
            'Vacation',
            $vacation->id,
            'translation_needed',
            'false',
            'true',
            'update',
            Auth::id(),
            'admin'
        );
    }

    /**
     * Get vacations that need translation
     */
    public function getVacationsNeedingTranslation(): array
    {
        // Get vacation IDs that are marked as needing translation
        $vacationIds = ModelChangeHistory::where('model_type', 'Vacation')
            ->where('field_name', 'translation_needed')
            ->where('new_value', 'true')
            ->where('created_at', '>', now()->subDays(7)) // Only look at recent changes
            ->pluck('model_id')
            ->unique()
            ->toArray();

        // Filter out vacations that have been translated recently
        $vacationsNeedingTranslation = [];
        
        foreach ($vacationIds as $vacationId) {
            $lastTranslation = ModelChangeHistory::where('model_type', 'Vacation')
                ->where('model_id', $vacationId)
                ->where('field_name', 'translation_completed')
                ->orderBy('created_at', 'desc')
                ->first();
            
            $lastChange = ModelChangeHistory::where('model_type', 'Vacation')
                ->where('model_id', $vacationId)
                ->where('field_name', 'translation_needed')
                ->where('new_value', 'true')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // If no translation completion record exists or last change is after last translation
            if (!$lastTranslation || ($lastChange && $lastChange->created_at > $lastTranslation->created_at)) {
                $vacationsNeedingTranslation[] = $vacationId;
            }
        }
        
        return $vacationsNeedingTranslation;
    }

    /**
     * Mark a vacation as translated
     */
    public function markVacationTranslated(Vacation $vacation, string $language): void
    {
        ModelChangeHistory::recordChange(
            'Vacation',
            $vacation->id,
            'translation_completed',
            null,
            $language,
            'update',
            null,
            'translation_service'
        );
    }

    /**
     * Get changed fields for a vacation since it was last translated
     */
    public function getChangedFieldsForVacation(Vacation $vacation): array
    {
        $lastTranslation = ModelChangeHistory::where('model_type', 'Vacation')
            ->where('model_id', $vacation->id)
            ->where('field_name', 'translation_completed')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $since = $lastTranslation ? $lastTranslation->created_at : now()->subDays(30);
        
        return ModelChangeHistory::where('model_type', 'Vacation')
            ->where('model_id', $vacation->id)
            ->where('created_at', '>', $since)
            ->whereIn('field_name', $this->translatableFields)
            ->where('source', 'admin')
            ->pluck('field_name')
            ->unique()
            ->toArray();
    }

    /**
     * Compare two values for equality
     */
    private function valuesAreEqual($value1, $value2): bool
    {
        // Handle null values
        if (is_null($value1) && is_null($value2)) {
            return true;
        }
        
        if (is_null($value1) || is_null($value2)) {
            return false;
        }
        
        // Handle array/object comparison
        if (is_array($value1) || is_object($value1)) {
            return json_encode($value1) === json_encode($value2);
        }
        
        // Handle string comparison
        return (string) $value1 === (string) $value2;
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStats(): array
    {
        $vacationsNeedingTranslation = $this->getVacationsNeedingTranslation();
        $totalVacations = Vacation::count();
        
        return [
            'total_vacations' => $totalVacations,
            'vacations_needing_translation' => count($vacationsNeedingTranslation),
            'translation_coverage' => $totalVacations > 0 
                ? round((($totalVacations - count($vacationsNeedingTranslation)) / $totalVacations) * 100, 2) 
                : 0
        ];
    }
} 