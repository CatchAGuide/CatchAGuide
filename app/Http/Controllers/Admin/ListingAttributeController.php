<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ListingAttributeRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingAttributeController extends Controller
{
    public function index(string $type): View
    {
        $config = $this->configOrFail($type);
        $modelClass = $config['model'];

        $query = $modelClass::query();
        if (ListingAttributeRegistry::hasField($type, 'sort_order')) {
            $query->orderBy('sort_order')->orderBy('id');
        } else {
            $query->orderByDesc('id');
        }

        $rows = $query->get();

        return view('admin.pages.setting.attributes.index', [
            'type' => $type,
            'config' => $config,
            'rows' => $rows,
            'fields' => $config['fields'],
            'pageTitle' => __('admin.listing_attributes.types.'.$config['label_key']),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $config = $this->configOrFail($type);
        $data = $this->validatedData($request, $type);
        $modelClass = $config['model'];

        $modelClass::create($data);
        ListingAttributeRegistry::flushCaches($type);

        return back()->with('success', __('admin.listing_attributes.flash.created'));
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $config = $this->configOrFail($type);
        $data = $this->validatedData($request, $type);
        $row = $this->findRow($config['model'], $id);

        $row->update($data);
        ListingAttributeRegistry::flushCaches($type);

        return back()->with('success', __('admin.listing_attributes.flash.updated'));
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $config = $this->configOrFail($type);
        $row = $this->findRow($config['model'], $id);

        $row->delete();
        ListingAttributeRegistry::flushCaches($type);

        return back()->with('success', __('admin.listing_attributes.flash.deleted'));
    }

    /**
     * Legacy alias entry points — resolve old path slug then delegate.
     */
    public function legacyIndex(Request $request): View
    {
        return $this->index($this->legacyTypeFromRequest($request));
    }

    public function legacyStore(Request $request): RedirectResponse
    {
        return $this->store($request, $this->legacyTypeFromRequest($request));
    }

    public function legacyUpdate(Request $request, int $id): RedirectResponse
    {
        return $this->update($request, $this->legacyTypeFromRequest($request), $id);
    }

    public function legacyDestroy(Request $request, int $id): RedirectResponse
    {
        return $this->destroy($this->legacyTypeFromRequest($request), $id);
    }

    private function legacyTypeFromRequest(Request $request): string
    {
        $legacySlug = (string) $request->route('legacySlug', '');

        return $this->resolveLegacyOrFail($legacySlug);
    }

    /**
     * @return array{model: class-string, group: string, label_key: string, fields: array<int, string>, caches: array<int, string>}
     */
    private function configOrFail(string $type): array
    {
        if (! ListingAttributeRegistry::has($type)) {
            throw new NotFoundHttpException;
        }

        return ListingAttributeRegistry::get($type);
    }

    private function resolveLegacyOrFail(string $legacySlug): string
    {
        $type = ListingAttributeRegistry::resolveLegacySlug($legacySlug);

        if ($type === null) {
            throw new NotFoundHttpException;
        }

        return $type;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function findRow(string $modelClass, int $id): Model
    {
        return $modelClass::query()->findOrFail($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, string $type): array
    {
        $fields = ListingAttributeRegistry::get($type)['fields'];
        $rules = [];

        foreach ($fields as $field) {
            $rules[$field] = match ($field) {
                'name', 'name_en', 'name_de' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
                'input_type' => 'nullable|string|max:50',
                'placeholder', 'placeholder_en' => 'nullable|string|max:255',
                default => 'nullable|string|max:255',
            };
        }

        // Prefer requiring at least one of EN/DE name fields
        if (in_array('name_en', $fields, true)) {
            $rules['name_en'] = 'required|string|max:255';
        } elseif (in_array('name', $fields, true)) {
            $rules['name'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);
        $data = [];

        foreach ($fields as $field) {
            if ($field === 'is_active') {
                $data['is_active'] = $request->boolean('is_active', true);
                continue;
            }

            if ($field === 'sort_order') {
                $data['sort_order'] = (int) $request->input('sort_order', 0);
                continue;
            }

            $data[$field] = $validated[$field] ?? null;
        }

        // Camp facilities: keep `name` in sync with English label (seeder convention)
        if ($type === 'camp-facilities') {
            $data['name'] = $data['name_en'] ?? $data['name'] ?? $data['name_de'] ?? '';
            if (empty($data['name_de']) && ! empty($data['name'])) {
                $data['name_de'] = $data['name'];
            }
        }

        return $data;
    }
}
