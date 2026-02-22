<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGuidingRequest;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use App\Models\Inclussion;
use App\Models\BoatExtras;
use App\Models\ExtrasPrice;
use App\Models\GuidingBoatType;
use App\Models\GuidingBoatDescription;
use App\Models\GuidingAdditionalInformation;
use App\Models\GuidingRequirements;
use App\Models\GuidingRecommendations;
use App\Models\Language;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuidingsController extends Controller
{
    /**
     * Allowed scalar fields for details modal (stored on guidings table or Language.json_data).
     */
    private const DETAILS_SCALAR_FIELDS = [
        'title',
        'description',
        'additional_information',
        'desc_course_of_action',
        'desc_meeting_point',
        'desc_starting_time',
        'desc_tour_unique',
    ];

    /**
     * List fields stored as JSON (guidings table keyed by id; Language.json_data as array).
     */
    private const DETAILS_LIST_FIELDS = [
        'requirements',
        'recommendations',
        'other_information',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $guidings = Guiding::all();
        return view('admin.pages.guidings.index',compact('guidings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = __('profile.creategiud');
        $locale = Config::get('app.locale');
        $nameField = $locale == 'en' ? 'name_en' : 'name';

        $modelClasses = [
            'targets' => Target::class,
            'methods' => Method::class,
            'waters' => Water::class, 
            'inclusions' => Inclussion::class,
            'boat_extras' => BoatExtras::class,
            'extras_prices' => ExtrasPrice::class,
            'guiding_boat_types' => GuidingBoatType::class,
            'guiding_boat_descriptions' => GuidingBoatDescription::class,
            'guiding_additional_infos' => GuidingAdditionalInformation::class,
            'guiding_requirements' => GuidingRequirements::class,
            'guiding_recommendations' => GuidingRecommendations::class
        ];

        $collections = [];
        foreach ($modelClasses as $key => $modelClass) {
            $collections[$key] = $modelClass::all()
            ->map(function($item) use ($nameField) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];
            });
        }
        
        return view('admin.pages.guidings.create', array_merge($collections, ['pageTitle' => $pageTitle, 'target_redirect' => route('admin.guidings.index')]));
        // return view('admin.pages.guidings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function show(Guiding $guiding)
    {
        $targets = Target::all();
        $methods = Method::all();
        $waters = Water::all();
        return view('admin.pages.guidings.show', compact('guiding', 'waters', 'methods', 'targets'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function edit(Guiding $guiding)
    {
        // Load necessary relationships
        $guiding->load([
            'guidingTargets', 'guidingWaters', 'guidingMethods', 
            'fishingTypes', 'fishingFrom'
        ]);

        // Prepare data for the form
        $formData = [
            'id' => $guiding->id,
            'is_update' => 1,
            //step1
            'title' => $guiding->title,
            'location' => $guiding->location,
            'latitude' => $guiding->lat,
            'longitude' => $guiding->lng,
            'country' => $guiding->country,
            'city' => $guiding->city,
            'region' => $guiding->region,
            'gallery_images' => $guiding->gallery_images,
            'thumbnail_path' => $guiding->thumbnail_path,

            //step 2
            'type_of_fishing' => $guiding->is_boat ? 'boat' : 'shore',
            'boat_type' => $guiding->boat_type,
            'boat_information' => $guiding->getBoatInformationAttribute(),
            'boat_extras' => $guiding->getBoatExtras(),

            //step 3
            'target_fish' => $guiding->getTargetFishNames(),
            'methods' => $guiding->getFishingMethodNames(),
            'water_types' => $guiding->getWaterNames(),

            //step 4
            'inclusions' => $guiding->getInclusionNames(),
            'fishing_type' => $guiding->fishing_type_id,

            //step 5
            'long_description' => $guiding->description,
            'desc_course_of_action' => $guiding->desc_course_of_action,
            'desc_starting_time' => $guiding->desc_starting_time,
            'desc_departure_time' => json_decode($guiding->desc_departure_time, true),
            'desc_meeting_point' => $guiding->desc_meeting_point,
            'desc_tour_unique' => $guiding->desc_tour_unique,
            
            //step 6
            'requirements' => $guiding->getRequirementsAttribute(),
            'recommendations' => $guiding->getRecommendationsAttribute(),
            'other_information' => $guiding->getOtherInformationAttribute(),

            //step 7
            'tour_type' => trim($guiding->tour_type),
            'duration' => $guiding->duration,
            'duration_type' => $guiding->duration_type,
            'no_guest' => $guiding->max_guests,
            // 'min_guests' => $guiding->min_guests,
            'price_type' => $guiding->price_type,
            'price' => $guiding->price,
            'prices' => json_decode($guiding->prices, true),
            'pricing_extra' => $guiding->getPricingExtraAttribute(),

            //step 8
            'allowed_booking_advance' => $guiding->allowed_booking_advance,
            'booking_window' => $guiding->booking_window,
            'seasonal_trip' => $guiding->seasonal_trip,
            'months' => json_decode($guiding->months, true),
            'other_boat_info' => $guiding->additional_information,
            'weekday_availability' => $guiding->weekday_availability,
            'weekdays' => json_decode($guiding->weekdays, true),
        ];

        $locale = Config::get('app.locale');
        $nameField = $locale == 'en' ? 'name_en' : 'name';

        $modelClasses = [
            'targets' => Target::class,
            'methods' => Method::class,
            'waters' => Water::class,
            'inclusions' => Inclussion::class,
            'boat_extras' => BoatExtras::class,
            'extras_prices' => ExtrasPrice::class,
            'guiding_boat_types' => GuidingBoatType::class,
            'guiding_boat_descriptions' => GuidingBoatDescription::class,
            'guiding_additional_infos' => GuidingAdditionalInformation::class,
            'guiding_requirements' => GuidingRequirements::class,
            'guiding_recommendations' => GuidingRecommendations::class
        ];

        $collections = [];
        foreach ($modelClasses as $key => $modelClass) {
            $collections[$key] = $modelClass::all()
            ->map(function($item) use ($nameField, $key) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];

            });
        }

        $pageTitle = __('profile.editguiding');

        return view('admin.pages.guidings.edit', array_merge(
            ['formData' => $formData, 'pageTitle' => $pageTitle, 'target_redirect' => route('admin.guidings.index')],
            $collections
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGuidingRequest $request, Guiding $guiding)
    {
       $data = $request->validated();

       $guiding->update($data);

       return redirect()->route('admin.guidings.index');
    }

    /**
     * Return guiding text details for the modal: main language from guidings table,
     * other languages from languages.json_data (type = guidings, source_id = guiding.id).
     */
    public function details(Guiding $guiding)
    {
        $mainLanguage = $guiding->language ?? 'de';
        $main = $this->buildGuidingTextPayload($guiding);

        $translations = [];
        $languageRecords = Language::where('source_id', (string) $guiding->id)
            ->where('type', 'guidings')
            ->get();

        foreach ($languageRecords as $record) {
            $jsonData = $record->json_data;
            if (! is_array($jsonData)) {
                $jsonData = is_string($jsonData) ? json_decode($jsonData, true) : [];
            }
            $translations[$record->language] = array_merge($main, is_array($jsonData) ? $jsonData : []);
        }

        return response()->json([
            'main_language' => $mainLanguage,
            'main' => $main,
            'translations' => $translations,
            'available_languages' => array_values(array_unique(array_merge([$mainLanguage], array_keys($translations)))),
        ]);
    }

    /**
     * Update a single field from the details modal (main table or translation).
     * Does not touch any other columns/keys.
     */
    public function updateDetailsField(Request $request, Guiding $guiding)
    {
        $mainLanguage = $guiding->language ?? 'de';
        $language = $request->input('language');
        $field = $request->input('field');
        $value = $request->input('value');
        $listIndex = $request->input('list_index'); // 0-based index for list items (translations)
        $listId = $request->input('list_id');       // id key for list items (main language, guidings table)

        $scalarFields = self::DETAILS_SCALAR_FIELDS;
        $listFields = self::DETAILS_LIST_FIELDS;

        $isListField = in_array($field, $listFields, true);
        $isScalarField = in_array($field, $scalarFields, true);

        $rules = [
            'language' => ['required', 'string', 'size:2'],
            'field'    => ['required', 'string', Rule::in(array_merge($scalarFields, $listFields))],
            'value'    => ['nullable', 'string'],
        ];
        if ($isListField) {
            $rules['list_index'] = ['nullable', 'integer', 'min:0'];
            $rules['list_id']    = ['nullable', 'string'];
        }
        $validated = $request->validate($rules);

        $language = $validated['language'];
        $field = $validated['field'];
        $value = $validated['value'] ?? '';

        if ($language === $mainLanguage) {
            // Save to guidings table only (single column / single list entry)
            if ($isScalarField) {
                if (! in_array($field, $scalarFields, true)) {
                    return response()->json(['error' => 'Invalid field'], 422);
                }
                $guiding->{$field} = $value;
                $guiding->save();
            } else {
                // List field on main table: raw attribute is JSON keyed by id (from DB to avoid accessor)
                $raw = DB::table('guidings')->where('id', $guiding->id)->value($field);
                $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                if (! is_array($decoded)) {
                    $decoded = [];
                }
                $listId = $request->input('list_id');
                if ($listId !== null && $listId !== '') {
                    if (isset($decoded[$listId])) {
                        $entry = $decoded[$listId];
                        if (is_array($entry)) {
                            $decoded[$listId]['value'] = $value;
                        } else {
                            $decoded[$listId] = $value;
                        }
                    }
                    $guiding->setAttribute($field, $decoded);
                    $guiding->save();
                } else {
                    return response()->json(['error' => 'list_id required for list field on main language'], 422);
                }
            }
        } else {
            // Save to Language table (json_data) – only update the one key/index
            $record = Language::where('source_id', (string) $guiding->id)
                ->where('type', 'guidings')
                ->where('language', $language)
                ->first();

            $jsonData = $record ? (is_array($record->json_data) ? $record->json_data : json_decode($record->json_data, true)) : [];
            if (! is_array($jsonData)) {
                $jsonData = [];
            }

            if ($isScalarField) {
                $jsonData[$field] = $value;
                $payload = $jsonData;
            } else {
                $listIndex = $listIndex !== null ? (int) $listIndex : null;
                if ($listIndex === null) {
                    return response()->json(['error' => 'list_index required for list field in translation'], 422);
                }
                $arr = isset($jsonData[$field]) && is_array($jsonData[$field]) ? $jsonData[$field] : [];
                $arr = array_values($arr);
                if (isset($arr[$listIndex])) {
                    $item = $arr[$listIndex];
                    if (is_array($item)) {
                        $item['value'] = $value;
                        $arr[$listIndex] = $item;
                    } else {
                        $arr[$listIndex] = $value;
                    }
                }
                $jsonData[$field] = $arr;
                $payload = $jsonData;
            }

            Language::updateOrCreate(
                [
                    'source_id' => (string) $guiding->id,
                    'type'      => 'guidings',
                    'language'  => $language,
                ],
                [
                    'title'     => $payload['title'] ?? ($record ? $record->title : $guiding->title),
                    'json_data' => $payload,
                ]
            );

            $cacheKey = 'guiding_translation_' . $guiding->id . '_' . $language;
            Cache::forget($cacheKey);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Build text fields from the guiding (main language) for modal display.
     */
    private function buildGuidingTextPayload(Guiding $guiding): array
    {
        $inclusions = $guiding->inclusions;
        $inclusions = is_string($inclusions) ? json_decode($inclusions, true) : $inclusions;
        $requirements = $guiding->requirements;
        $requirements = is_string($requirements) ? json_decode($requirements, true) : $requirements;
        $recommendations = $guiding->recommendations;
        $recommendations = is_string($recommendations) ? json_decode($recommendations, true) : $recommendations;
        $otherInformation = $guiding->other_information;
        $otherInformation = is_string($otherInformation) ? json_decode($otherInformation, true) : $otherInformation;
        $pricingExtra = $guiding->pricing_extra;
        $pricingExtra = is_string($pricingExtra) ? json_decode($pricingExtra, true) : $pricingExtra;

        // Only text-based fields from multi-step-form (no location/city/region/country, no departure_times).
        return array_filter([
            'title' => $guiding->title,
            'description' => $guiding->description,
            'additional_information' => $guiding->additional_information,
            'desc_course_of_action' => $guiding->desc_course_of_action,
            'desc_meeting_point' => $guiding->desc_meeting_point,
            'desc_starting_time' => $guiding->desc_starting_time,
            'desc_tour_unique' => $guiding->desc_tour_unique,
            'inclusions' => $inclusions,
            'requirements' => $requirements,
            'recommendations' => $recommendations,
            'other_information' => $otherInformation,
            'pricing_extra' => $pricingExtra,
        ], function ($v) {
            return $v !== null && $v !== '';
        });
    }

    public function changeGuidingStatus($id)
    {
        $guiding = Guiding::find($id);
        if($guiding->status === 1) {
            $guiding->status = 0;
        } else {
            $guiding->status = 1;
        }
        $guiding->save();
        return back()->with('success', 'Der Status wurde erfolgreich geändert');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function destroy(Guiding $guiding)
    {
        //
    }
}
