<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomScheduledTask;
use App\Services\ScheduledTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduledTasksController extends Controller
{
    private function weekdayOptions(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    /**
     * @return array<string, array<int, mixed|\Closure|string>>
     */
    private function scheduleFieldRules(Request $request, ScheduledTaskService $scheduledTasks): array
    {
        $freqKeys = $scheduledTasks->allowedFrequencyKeys();

        return [
            'is_enabled' => ['required', 'boolean'],
            'frequency' => ['required', Rule::in($freqKeys)],
            'schedule_time' => [
                Rule::requiredIf(fn () => in_array($request->input('frequency'), ['daily_at', 'weekly'], true)),
                'nullable',
                'string',
                'regex:/^([01]?\d|2[0-3]):[0-5]\d$/',
            ],
            'day_of_week' => [
                Rule::requiredIf(fn () => $request->input('frequency') === 'weekly'),
                'nullable',
                'integer',
                'between:0,6',
            ],
            'cron_expression' => [
                Rule::requiredIf(fn () => $request->input('frequency') === 'cron'),
                'nullable',
                'string',
                'max:64',
                'regex:/^(\S+\s+){4}\S+$/',
            ],
        ];
    }

    private function commandDangerRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (!is_string($value) || $value === '') {
                return;
            }
            if (preg_match('/[;&|`$<>\\n\\r\\\\]/', $value)) {
                $fail('The command contains characters that are not allowed.');
            }
        };
    }

    public function index(ScheduledTaskService $scheduledTasks): View
    {
        return view('admin.pages.setting.scheduled-tasks.index', [
            'tasks' => $scheduledTasks->allForAdmin(),
            'frequencies' => config('scheduled_tasks.frequencies', []),
            'weekdays' => $this->weekdayOptions(),
        ]);
    }

    public function update(Request $request, string $key, ScheduledTaskService $scheduledTasks): RedirectResponse
    {
        if (!isset(config('scheduled_tasks.tasks')[$key])) {
            abort(404);
        }

        $rules = $this->scheduleFieldRules($request, $scheduledTasks);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('editing_scheduled_task_key', $key);
        }

        $scheduledTasks->saveFromAdmin($key, $validator->validated());

        return redirect()->back()->with('success', 'Scheduled task saved.');
    }

    public function storeCustom(Request $request, ScheduledTaskService $scheduledTasks): RedirectResponse
    {
        $rules = array_merge(
            [
                'label' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'command' => ['required', 'string', 'max:500', $this->commandDangerRule()],
                'append_output_to' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\-\.\/]+$/'],
            ],
            $this->scheduleFieldRules($request, $scheduledTasks)
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('open_add_custom_modal', true);
        }

        $data = $validator->validated();
        $data['without_overlapping'] = $request->boolean('without_overlapping');
        $data['run_in_background'] = $request->boolean('run_in_background');
        $scheduledTasks->createCustom($data);

        return redirect()->back()->with('success', 'Custom scheduled task added.');
    }

    public function updateCustom(Request $request, CustomScheduledTask $customScheduledTask, ScheduledTaskService $scheduledTasks): RedirectResponse
    {
        $key = 'custom_' . $customScheduledTask->id;

        $rules = array_merge(
            [
                'label' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'command' => ['required', 'string', 'max:500', $this->commandDangerRule()],
                'append_output_to' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\-\.\/]+$/'],
            ],
            $this->scheduleFieldRules($request, $scheduledTasks)
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('editing_scheduled_task_key', $key);
        }

        $data = $validator->validated();
        $data['without_overlapping'] = $request->boolean('without_overlapping');
        $data['run_in_background'] = $request->boolean('run_in_background');
        $scheduledTasks->updateCustom($customScheduledTask, $data);

        return redirect()->back()->with('success', 'Custom scheduled task saved.');
    }

    public function destroyCustom(CustomScheduledTask $customScheduledTask): RedirectResponse
    {
        $customScheduledTask->delete();

        return redirect()->back()->with('success', 'Custom scheduled task removed.');
    }
}
