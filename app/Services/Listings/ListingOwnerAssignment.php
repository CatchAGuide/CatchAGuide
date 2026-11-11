<?php

namespace App\Services\Listings;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Separates the user/provider who owns a listing from the staff member who created it.
 *
 * Admin routes authenticate the employees guard, then Auth::id() is that
 * employee's primary key. Saving it as user_id would attach the listing to
 * whichever user happens to share the number — never treat employee ids as owners.
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
        $submitted = $request->exists('user_id');
        $requested = $this->positiveInt($request->input('user_id'));

        if ($employee instanceof Employee) {
            $employeeId = (int) $employee->getKey();

            return [
                'user_id' => $submitted
                    ? $this->ownerIdFromSubmission($requested, $employeeId)
                    : $this->keptOwnerId($existing),
                'employee_id' => $this->existingEmployeeId($existing) ?? $employeeId,
            ];
        }

        return [
            'user_id' => $this->realOwnerId($requested)
                ?? $this->positiveInt(Auth::guard('web')->id())
                ?? $this->keptOwnerId($existing),
            'employee_id' => $this->existingEmployeeId($existing),
        ];
    }

    /**
     * Explicit picker submit: empty / employee id clears the owner; a real user id assigns them.
     */
    private function ownerIdFromSubmission(?int $requested, int $employeeId): ?int
    {
        if ($requested === null || $requested === $employeeId) {
            return null;
        }

        return $this->realOwnerId($requested);
    }

    /**
     * A stored owner stays on the listing. A stored id that is really an
     * employee primary key does not.
     */
    private function keptOwnerId(?Model $existing): ?int
    {
        $current = $this->positiveInt($existing?->getAttribute('user_id'));
        if ($current === null || $this->isEmployeeId($current)) {
            return null;
        }

        return User::query()->whereKey($current)->exists() ? $current : null;
    }

    private function realOwnerId(?int $id): ?int
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
