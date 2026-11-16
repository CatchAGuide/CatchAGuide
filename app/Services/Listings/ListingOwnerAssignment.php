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
        $explicit = $request->boolean('listing_owner_assigned');

        if ($employee instanceof Employee) {
            $employeeId = (int) $employee->getKey();

            return [
                'user_id' => $submitted
                    ? $this->ownerIdFromSubmission($requested, $employeeId, $explicit)
                    : $this->keptOwnerId($existing),
                'employee_id' => $this->existingEmployeeId($existing) ?? $employeeId,
            ];
        }

        return [
            'user_id' => $this->realUserId($requested)
                ?? $this->positiveInt(Auth::guard('web')->id())
                ?? $this->keptOwnerId($existing),
            'employee_id' => $this->existingEmployeeId($existing),
        ];
    }

    /**
     * Picker submit: an empty value clears the owner. A chosen user is stored
     * even when an employee row happens to use the same number.
     *
     * Without the picker flag, an employee id is still rejected. Older admin
     * forms posted Auth::id() from the employees guard into user_id, which
     * attached the listing to whichever customer shared that number.
     */
    private function ownerIdFromSubmission(?int $requested, int $employeeId, bool $explicit): ?int
    {
        if ($requested === null) {
            return null;
        }

        if (! $explicit && ($requested === $employeeId || $this->isEmployeeId($requested))) {
            return null;
        }

        return $this->realUserId($requested);
    }

    /**
     * A stored owner stays on the listing when that user still exists.
     * Users and employees are separate tables, so a shared number is not
     * proof the row was the old Auth::id() mistake.
     */
    private function keptOwnerId(?Model $existing): ?int
    {
        return $this->realUserId($this->positiveInt($existing?->getAttribute('user_id')));
    }

    private function realUserId(?int $id): ?int
    {
        if ($id === null) {
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
