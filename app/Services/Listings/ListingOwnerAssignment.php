<?php

namespace App\Services\Listings;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Separates the guide who owns a listing from the staff member who created it.
 *
 * Admin routes authenticate the employees guard, then Auth::id() is that
 * employee's primary key. Saving it as user_id attaches the listing to
 * whichever guide happens to share the number.
 */
class ListingOwnerAssignment
{
    /** @var list<int>|null */
    private ?array $employeeIds = null;

    /**
     * @return array{user_id: ?int, employee_id: ?int}
     */
    public function forRequest(Request $request, ?Model $existing = null): array
    {
        $employee = Auth::guard('employees')->user();
        $requested = $this->positiveInt($request->input('user_id'));

        if ($employee instanceof Employee) {
            $employeeId = (int) $employee->getKey();

            return [
                'user_id' => $this->guideIdWhenAdmin($requested, $employeeId, $existing),
                'employee_id' => $this->existingEmployeeId($existing) ?? $employeeId,
            ];
        }

        return [
            'user_id' => $this->realGuideId($requested)
                ?? $this->positiveInt(Auth::guard('web')->id())
                ?? $this->keptGuideId($existing),
            'employee_id' => $this->existingEmployeeId($existing),
        ];
    }

    private function guideIdWhenAdmin(?int $requested, int $employeeId, ?Model $existing): ?int
    {
        $explicit = $this->realGuideId($requested);
        if ($explicit !== null && $explicit !== $employeeId) {
            return $explicit;
        }

        return $this->keptGuideId($existing);
    }

    /**
     * A stored guide stays on the listing. A stored id that is really an
     * employee primary key does not.
     */
    private function keptGuideId(?Model $existing): ?int
    {
        $current = $this->positiveInt($existing?->getAttribute('user_id'));
        if ($current === null || $this->isEmployeeId($current)) {
            return null;
        }

        return User::query()->whereKey($current)->exists() ? $current : null;
    }

    private function realGuideId(?int $id): ?int
    {
        if ($id === null || $this->isEmployeeId($id)) {
            return null;
        }

        return User::query()->whereKey($id)->exists() ? $id : null;
    }

    private function existingEmployeeId(?Model $existing): ?int
    {
        return $this->positiveInt($existing?->getAttribute('employee_id'));
    }

    private function isEmployeeId(int $id): bool
    {
        $this->employeeIds ??= Employee::withTrashed()
            ->pluck('id')
            ->map(fn ($value) => (int) $value)
            ->all();

        return in_array($id, $this->employeeIds, true);
    }

    private function positiveInt(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }
}
