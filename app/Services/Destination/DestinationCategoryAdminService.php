<?php

namespace App\Services\Destination;

use App\Domain\Destination\DestinationCategoryType;
use App\Models\Destination;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

/**
 * Persists destination rows used for public category pages (vacations-style).
 * Single responsibility: admin CRUD for typed destinations + related tables.
 */
class DestinationCategoryAdminService
{
    public function paginateForType(string $type, int $perPage = 25): LengthAwarePaginator
    {
        $this->assertKnownType($type);

        return Destination::whereType($type)->paginate($perPage);
    }

    public function findForEdit(int $id, string $type): ?Destination
    {
        $this->assertKnownType($type);

        $row = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])->find($id);

        if ($row === null || $row->type !== $type) {
            return null;
        }

        return $row;
    }

    public function store(Request $request, string $type): void
    {
        $this->assertKnownType($type);
        $request->validate($this->validationRules($type));

        DB::beginTransaction();

        try {
            $data = $request->only([
                'language', 'name', 'title', 'sub_title', 'introduction',
                'fish_avail_title', 'fish_avail_intro',
                'size_limit_title', 'size_limit_intro',
                'time_limit_title', 'time_limit_intro',
                'faq_title',
            ]);
            $data['type'] = $type;
            $data['slug'] = $this->slugFormat($request->name);
            $data['filters'] = json_encode($request->filters);
            $data['content'] = $request->body;
            $data['countrycode'] = $request->countrycode ?? null;

            if ($request->hasFile('thumbnailImage')) {
                $data['thumbnail_path'] = $this->uploadThumbnail($request->file('thumbnailImage'));
            }

            $destination = Destination::create($data);

            $this->syncRelatedCreates($request, $destination->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Request $request, int $id, string $type): void
    {
        $this->assertKnownType($type);
        $request->validate($this->validationRules($type, $id));

        DB::beginTransaction();

        try {
            $data = $request->only([
                'language', 'name', 'title', 'sub_title', 'introduction',
                'fish_avail_title', 'fish_avail_intro',
                'size_limit_title', 'size_limit_intro',
                'time_limit_title', 'time_limit_intro',
                'faq_title',
            ]);
            $data['slug'] = $this->slugFormat($request->name);
            $data['filters'] = json_encode($request->filters);
            $data['content'] = $request->body;
            $data['countrycode'] = $request->countrycode ?? null;

            if ($request->hasFile('thumbnailImage')) {
                $data['thumbnail_path'] = $this->uploadThumbnail($request->file('thumbnailImage'));
            }

            Destination::where('id', $id)->where('type', $type)->update($data);

            $this->syncRelatedUpdates($request, $id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(int $id, string $type): void
    {
        $this->assertKnownType($type);
        Destination::where('id', $id)->where('type', $type)->delete();
    }

    public function uploadThumbnail($thumbnailImage): string
    {
        $thumbnail_path = $thumbnailImage->store('public');
        $imagePath = Storage::disk()->path($thumbnail_path);

        $image = Image::make($imagePath);
        $webpImageName = pathinfo($thumbnail_path, PATHINFO_FILENAME) . '.webp';
        $webpImage = $image->encode('webp', 75);

        $webp_path = 'blog/country/';

        if (! Storage::disk('public_path')->exists($webp_path)) {
            Storage::disk('public_path')->makeDirectory($webp_path);
        }

        $webp_path .= $webpImageName;

        Storage::disk('public_path')->put($webp_path, $webpImage->encoded);
        $webpImage->save(public_path($webp_path));

        return $webp_path;
    }

    public function slugFormat(string $value): string
    {
        return str_replace(' ', '-', strtolower($value));
    }

    /**
     * @return array<string, mixed>
     */
    public function formDataForCreate(string $formLabel, string $storeRouteName): array
    {
        return array_merge(
            [
                'form' => $formLabel,
                'route' => route($storeRouteName),
                'method' => '',
            ],
            $this->blankFormFields()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function formDataForEdit(Destination $row, string $formLabel, string $updateRouteName): array
    {
        $filter = json_decode($row->filters, true) ?? [];

        return [
            'form' => $formLabel,
            'route' => route($updateRouteName, $row->id),
            'method' => 'PUT',
            'language' => $row->language,
            'countrycode' => $row->countrycode,
            'name' => $row->name,
            'thumbnail' => $row->getThumbnailPath(),
            'title' => $row->title,
            'sub_title' => $row->sub_title,
            'introduction' => $row->introduction,
            'body' => $row->content,
            'place' => $filter['place'] ?? '',
            'placeLat' => $filter['placeLat'] ?? '',
            'placeLng' => $filter['placeLng'] ?? '',
            'country' => $filter['country'] ?? '',
            'city' => $filter['city'] ?? '',
            'region' => $filter['region'] ?? '',
            'fish_chart' => $row->fish_chart,
            'fish_avail_title' => $row->fish_avail_title,
            'fish_avail_intro' => $row->fish_avail_intro,
            'fish_size_limit' => $row->fish_size_limit,
            'size_limit_title' => $row->size_limit_title,
            'size_limit_intro' => $row->size_limit_intro,
            'fish_time_limit' => $row->fish_time_limit,
            'time_limit_title' => $row->time_limit_title,
            'time_limit_intro' => $row->time_limit_intro,
            'faq' => $row->faq,
            'faq_title' => $row->faq_title,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(string $type, ?int $id = null): array
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('destinations', 'name')
                    ->where('type', $type)
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'filters' => 'required',
        ];
    }

    private function assertKnownType(string $type): void
    {
        if (! in_array($type, [DestinationCategoryType::VACATIONS, DestinationCategoryType::TRIPS], true)) {
            throw new \InvalidArgumentException('Unsupported destination category type: ' . $type);
        }
    }

    private function syncRelatedCreates(Request $request, int $destinationId): void
    {
        $language = $request->input('language');

        if ($request->has('fish_chart')) {
            foreach ($request->fish_chart as $value) {
                $value['destination_id'] = $destinationId;
                $value['language'] = $language;
                DestinationFishChart::create($value);
            }
        }

        if ($request->has('fish_size_limit')) {
            foreach ($request->fish_size_limit as $value) {
                $value['destination_id'] = $destinationId;
                $value['language'] = $language;
                DestinationFishSizeLimit::create($value);
            }
        }

        if ($request->has('fish_time_limit')) {
            foreach ($request->fish_time_limit as $value) {
                $value['destination_id'] = $destinationId;
                $value['language'] = $language;
                DestinationFishTimeLimit::create($value);
            }
        }

        if ($request->has('faq')) {
            foreach ($request->faq as $value) {
                $value['destination_id'] = $destinationId;
                $value['language'] = $language;
                DestinationFaq::create($value);
            }
        }
    }

    private function syncRelatedUpdates(Request $request, int $destinationId): void
    {
        $language = $request->input('language');

        if ($request->has('fish_chart')) {
            foreach ($request->fish_chart as $value) {
                $value['language'] = $language;
                if ((int) ($value['id'] ?? 0) === 0) {
                    $value['destination_id'] = $destinationId;
                    unset($value['id']);
                    DestinationFishChart::create($value);
                } else {
                    DestinationFishChart::whereId($value['id'])->update($value);
                }
            }
        }

        if ($request->has('fish_size_limit')) {
            foreach ($request->fish_size_limit as $value) {
                $value['language'] = $language;
                if ((int) ($value['id'] ?? 0) === 0) {
                    $value['destination_id'] = $destinationId;
                    unset($value['id']);
                    DestinationFishSizeLimit::create($value);
                } else {
                    DestinationFishSizeLimit::whereId($value['id'])->update($value);
                }
            }
        }

        if ($request->has('fish_time_limit')) {
            foreach ($request->fish_time_limit as $value) {
                $value['language'] = $language;
                if ((int) ($value['id'] ?? 0) === 0) {
                    $value['destination_id'] = $destinationId;
                    unset($value['id']);
                    DestinationFishTimeLimit::create($value);
                } else {
                    DestinationFishTimeLimit::whereId($value['id'])->update($value);
                }
            }
        }

        if ($request->has('faq')) {
            foreach ($request->faq as $value) {
                $value['language'] = $language;
                if ((int) ($value['id'] ?? 0) === 0) {
                    $value['destination_id'] = $destinationId;
                    unset($value['id']);
                    DestinationFaq::create($value);
                } else {
                    DestinationFaq::whereId($value['id'])->update($value);
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function blankFormFields(): array
    {
        return [
            'language' => old('language'),
            'countrycode' => old('countrycode'),
            'name' => old('name'),
            'thumbnail' => 'https://place-hold.it/300x300',
            'title' => old('title'),
            'sub_title' => old('sub_title'),
            'introduction' => old('introduction'),
            'body' => old('body'),
            'place' => old('filters.place', ''),
            'placeLat' => old('filters.placeLat', ''),
            'placeLng' => old('filters.placeLng', ''),
            'country' => old('filters.country', ''),
            'city' => old('filters.city', ''),
            'region' => old('filters.region', ''),
            'fish_chart' => old('fish_chart'),
            'fish_avail_title' => old('fish_avail_title'),
            'fish_avail_intro' => old('fish_avail_intro'),
            'fish_size_limit' => old('fish_size_limit'),
            'size_limit_title' => old('size_limit_title'),
            'size_limit_intro' => old('size_limit_intro'),
            'fish_time_limit' => old('fish_time_limit'),
            'time_limit_title' => old('time_limit_title'),
            'time_limit_intro' => old('time_limit_intro'),
            'faq' => old('faq'),
            'faq_title' => old('faq_title'),
        ];
    }
}
