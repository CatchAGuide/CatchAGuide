<?php

namespace App\Services\Trip;

use App\Models\Trip;
use App\Models\TripAvailabilityDate;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Maps trip XLSX templates to existing trips table / admin form fields only.
 * Labels mirror the German trip form (resources/lang/de/trips.php + trip-form.blade.php).
 */
class TripXlsxImporter
{
    /** Laravel string() columns on trips — varchar(255). */
    private const VARCHAR_LIMIT = 255;

    private const PLACEHOLDER_PATTERNS = [
        '/^bitte beim anbieter erfragen$/iu',
        '/^bitte beim anbieter nachfragen$/iu',
        '/^please ask the provider$/iu',
        '/^please ask provider$/iu',
        '/^ask the provider$/iu',
        '/^kein fixer termin/i',
    ];

    private const NUMERIC_XLSX_LABELS = [
        'Anzahl Nächte',
        'Anzahl Tage (Angeltage)',
        'Gruppengröße Von (Min.)',
        'Gruppengröße Bis (Max.)',
        'Gesamtpreis standard pro Person',
        'Gesamtpreis bei Einzelzimmer',
        'Freie Plätze',
    ];

    /**
     * Same keys as trip-form.blade.php step 6 / TripDataProcessor::processAdditionalInfo().
     */
    private const ADDITIONAL_INFO_XLSX = [
        'Kinderfreundlich'                  => 'child_friendly',
        'Barrierefrei / Rollstuhlgerecht'   => 'accessible',
        'Rauchen erlaubt'                   => 'smoking_allowed',
        'Alkohol erlaubt'                   => 'alcohol_allowed',
        'Catch & Release Pflicht'           => 'catch_and_release',
        'Fangerfolg'                        => 'catch_success',
        'Lizenz / Genehmigung erforderlich' => 'license_required',
        'Kleidungsempfehlungen'             => 'clothing_recommendations',
        'Erfahrungslevel erforderlich'      => 'experience_level_required',
        'Ausrüstung mitbringen'             => 'equipment_to_bring',
        'Mindestalter'                      => 'minimum_age',
        'Maximalalter'                      => 'maximum_age',
        'Nicht-Angelaktivitäten'            => 'non_fishing_activities',
        'Küche / Essensstil'                => 'cuisine_food_style',
    ];

    public function __construct(
        private TripSeoService $seoService
    ) {}

    /**
     * @return array{trip: array<string, mixed>, availability_dates: array<int, array<string, mixed>>}
     */
    public function parseFile(string $path): array
    {
        $fields = $this->readFieldMap(IOFactory::load($path));

        return [
            'trip'               => $this->mapTripData($fields),
            'availability_dates' => $this->readAvailabilityFromFile($path),
        ];
    }

    public function importFile(
        string $path,
        int $userId,
        string $status = 'draft',
        bool $updateExisting = false
    ): Trip {
        $parsed = $this->parseFile($path);
        $tripData = $parsed['trip'];
        $tripData['user_id'] = $userId;
        $tripData['status'] = $status;
        $tripData['slug'] = $this->seoService->generateSlug($tripData['title'] ?? 'Untitled');

        $existing = null;
        if ($updateExisting) {
            $existing = Trip::where('user_id', $userId)
                ->where('title', $tripData['title'] ?? '')
                ->orderByDesc('id')
                ->first();
        }

        if (! $existing) {
            $existing = Trip::where('slug', $tripData['slug'])->first();
        }

        if ($existing && ! $updateExisting) {
            $tripData['slug'] = $this->seoService->generateSlug(
                ($tripData['title'] ?? 'Untitled') . ' ' . pathinfo($path, PATHINFO_FILENAME)
            );
            $existing = null;
        }

        if ($existing && $updateExisting) {
            $tripData['slug'] = $existing->slug;
            $existing->update($tripData);
            $trip = $existing->fresh();
        } else {
            $trip = Trip::create($tripData);
        }

        $trip->availabilityDates()->delete();
        foreach ($parsed['availability_dates'] as $row) {
            TripAvailabilityDate::create([
                'trip_id'         => $trip->id,
                'departure_date'  => $row['departure_date'],
                'spots_available' => $row['spots_available'],
                'status'          => 'available',
            ]);
        }

        return $trip->fresh(['availabilityDates']);
    }

    /**
     * @return array<string, string>
     */
    private function readFieldMap($spreadsheet): array
    {
        $fields = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
                $label = $this->cellText($sheet->getCell('A' . $row)->getCalculatedValue());
                $value = $this->cellText($sheet->getCell('B' . $row)->getCalculatedValue());

                if ($label === '' || $this->isSectionHeader($label) || $value === '') {
                    continue;
                }

                $fields[$label] = $value;
            }
        }

        return $fields;
    }

    /**
     * @param array<string, string> $fields
     * @return array<string, mixed>
     */
    private function mapTripData(array $fields): array
    {
        return [
            'title'                      => $this->limitVarchar($this->textValue($fields, 'Trip Titel')),
            'location'                   => $this->limitVarchar($this->textValue($fields, 'Ort / Location')),
            'target_species'             => $this->tagifyList($this->textValue($fields, 'Zielfischarten')),
            'fishing_methods'            => $this->tagifyList($this->textValue($fields, 'Angelmethoden')),
            'water_types'                => $this->tagifyList($this->textValue($fields, 'Gewässertypen')),
            'fishing_style'              => $this->normalizeFishingStyle($this->textValue($fields, 'Angelstil')),
            'skill_level'                => $this->tagifyList($this->normalizeSkillLevel($this->textValue($fields, 'Erfahrungslevel (Skill Level)'))),
            'duration_nights'            => $this->toInt($this->rawValue($fields, 'Anzahl Nächte')),
            'duration_days'              => $this->toInt($this->rawValue($fields, 'Anzahl Tage (Angeltage)')),
            'group_size_min'             => $this->toInt($this->rawValue($fields, 'Gruppengröße Von (Min.)')),
            'group_size_max'             => $this->toInt($this->rawValue($fields, 'Gruppengröße Bis (Max.)')),
            'trip_schedule'              => $this->mapTripSchedule($fields),
            'meeting_point'              => $this->nullableText($this->textValue($fields, 'Treffpunkt / Check-in Info')),
            'best_season_from'           => $this->normalizeMonthOption($this->textValue($fields, 'Beste Reisezeit Von')),
            'best_season_to'             => $this->normalizeMonthOption($this->textValue($fields, 'Beste Reisezeit Bis')),
            'catering'                   => $this->tagifyList($this->textValue($fields, 'Verpflegung (Catering)')),
            'best_arrival_options'       => $this->nullableVarchar($this->textValue($fields, 'Beste Anreisemöglichkeiten')),
            'arrival_day'                => $this->nullableVarchar($this->textValue($fields, 'Anreisetag')),
            'boat_type'                  => $this->nullableVarchar($this->textValue($fields, 'Boottyp')),
            'boat_features'              => $this->tagifyList($this->textValue($fields, 'Enthaltene Bootausstattung')),
            'boat_information'           => $this->nullableText($this->textValue($fields, 'Bootinformationen')),
            'accommodation_type'         => $this->nullableVarchar($this->textValue($fields, 'Unterkunftsart')),
            'accommodation_description'  => $this->nullableText($this->textValue($fields, 'Beschreibung der Unterkunft')),
            'room_types'                 => $this->tagifyList($this->textValue($fields, 'Verfügbare Zimmertypen')),
            'distance_to_water'          => $this->nullableVarchar($this->textValue($fields, 'Entfernung zum Wasser')),
            'nearest_airport'            => $this->nullableVarchar($this->textValue($fields, 'Nächstgelegener Flughafen')),
            'provider_experience'        => $this->nullableText($this->textValue($fields, 'Erfahrung des Anbieters')),
            'provider_certifications'    => $this->nullableText($this->textValue($fields, 'Zertifikate & Lizenzen')),
            'boat_staff'                 => $this->nullableVarchar($this->textValue($fields, 'Bootcrew')),
            'guide_languages'            => $this->tagifyList($this->textValue($fields, 'Sprachen des Guides')),
            'description'                => $this->nullableText($this->textValue($fields, 'Vollständige Beschreibung')),
            'trip_highlights'            => ['items' => $this->mapNumberedItems($fields, 'Highlight')],
            'included'                   => $this->tagifyList($this->joinNumberedItems($fields, 'Inklusive')),
            'excluded'                   => $this->tagifyList($this->joinNumberedItems($fields, 'Exklusive')),
            'additional_info'            => $this->mapAdditionalInfo($fields),
            'price_per_person'           => $this->toDecimal($this->rawValue($fields, 'Gesamtpreis standard pro Person')),
            'price_single_room_addition' => $this->toDecimal($this->rawValue($fields, 'Gesamtpreis bei Einzelzimmer')),
            'currency'                   => strtoupper(substr($this->textValue($fields, 'Währung') ?: 'EUR', 0, 3)),
            'downpayment_policy'         => $this->nullablePolicyText($this->textValue($fields, 'Anzahlungsrichtlinie')),
            'cancellation_policy'        => $this->nullablePolicyText($this->textValue($fields, 'Stornierungsbedingungen')),
        ];
    }

    /**
     * @param array<string, string> $fields
     */
    private function mapTripSchedule(array $fields): array
    {
        $schedule = [];

        for ($day = 1; $day <= 14; $day++) {
            $label = $this->textValue($fields, "Tag {$day} – Label") ?: $this->textValue($fields, "Tag {$day} - Label");
            $time = $this->textValue($fields, "Tag {$day} – Uhrzeit") ?: $this->textValue($fields, "Tag {$day} - Uhrzeit");
            $description = $this->textValue($fields, "Tag {$day} – Beschreibung") ?: $this->textValue($fields, "Tag {$day} - Beschreibung");

            if ($label === '' && $description === '') {
                continue;
            }

            $schedule[] = [
                'time'        => $time !== '' ? $time : null,
                'day_label'   => $label,
                'description' => $description,
            ];
        }

        return $schedule;
    }

    /**
     * @param array<string, string> $fields
     * @return array<string, array{enabled: bool, details: ?string}>
     */
    private function mapAdditionalInfo(array $fields): array
    {
        $result = [];

        foreach (self::ADDITIONAL_INFO_XLSX as $label => $key) {
            $details = $this->textValue($fields, $label);
            $result[$key] = [
                'enabled' => $details !== '',
                'details' => $details !== '' ? $details : null,
            ];
        }

        return $result;
    }

    /**
     * @param array<string, string> $fields
     * @return list<string>
     */
    private function mapNumberedItems(array $fields, string $prefix): array
    {
        $items = [];

        for ($i = 1; $i <= 20; $i++) {
            $value = $this->textValue($fields, "{$prefix} {$i}");
            if ($value !== '') {
                $items[] = $value;
            }
        }

        return $items;
    }

    /**
     * @param array<string, string> $fields
     */
    private function joinNumberedItems(array $fields, string $prefix): string
    {
        return implode(', ', $this->mapNumberedItems($fields, $prefix));
    }

    /**
     * @param array<string, string> $fields
     */
    private function rawValue(array $fields, string $label): string
    {
        return trim($fields[$label] ?? '');
    }

    /**
     * @param array<string, string> $fields
     */
    private function textValue(array $fields, string $label): string
    {
        $value = $this->rawValue($fields, $label);

        if ($value === '') {
            return '';
        }

        if (in_array($label, self::NUMERIC_XLSX_LABELS, true) && $this->isPlaceholder($value)) {
            return '';
        }

        return $value;
    }

    private function nullableText(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    private function nullablePolicyText(string $value): ?string
    {
        $value = trim($value);

        return ($value !== '' && ! $this->isPlaceholder($value)) ? $value : null;
    }

    private function nullableVarchar(string $value): ?string
    {
        return $value !== '' ? $this->limitVarchar($value) : null;
    }

    private function limitVarchar(string $value): string
    {
        return Str::limit($value, self::VARCHAR_LIMIT, '…');
    }

    private function isPlaceholder(string $value): bool
    {
        foreach (self::PLACEHOLDER_PATTERNS as $pattern) {
            if (preg_match($pattern, trim($value))) {
                return true;
            }
        }

        return false;
    }

    private function isSectionHeader(string $label): bool
    {
        return str_starts_with($label, 'Seite ')
            || in_array($label, [
                'Feld',
                'Angeldetails',
                'Reisedauer & Gruppe',
                'Reiseplan (Tagesplan)',
                'Logistik & Saison',
                'Boot',
                'Unterkunft',
                'Anbieter / Guide',
                'Standort',
                'Inklusive (Was ist im Preis enthalten?)',
                'Exklusive (Was ist NICHT im Preis enthalten?)',
            ], true)
            || preg_match('/^Termin \d+$/', $label) === 1;
    }

    /**
     * @return list<array{id: null, name: string}>
     */
    private function tagifyList(string $value): array
    {
        if ($value === '') {
            return [];
        }

        $parts = str_contains($value, ';')
            ? array_filter(array_map('trim', explode(';', $value)))
            : $this->splitCommaList($value);

        return array_values(array_map(
            fn (string $name) => ['id' => null, 'name' => $name],
            array_filter(array_map('trim', $parts))
        ));
    }

    /**
     * @return list<string>
     */
    private function splitCommaList(string $value): array
    {
        $parts = [];
        $current = '';
        $depth = 0;

        for ($i = 0, $length = strlen($value); $i < $length; $i++) {
            $char = $value[$i];

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth = max(0, $depth - 1);
            }

            if ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

    private function normalizeFishingStyle(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $normalized = Str::lower(trim(explode('(', $value)[0]));

        return match ($normalized) {
            'active', 'aktiv'   => 'active',
            'passive', 'passiv' => 'passive',
            'both', 'beides', 'aktiv & passiv', 'active & passive', 'aktiv / passiv', 'active / passive' => 'both',
            default             => $normalized,
        };
    }

    private function normalizeSkillLevel(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $lower = Str::lower($value);

        return match (true) {
            str_contains($lower, 'all levels'), str_contains($lower, 'alle') => 'all_levels',
            str_contains($lower, 'beginner'), str_contains($lower, 'anfänger') => 'beginner',
            str_contains($lower, 'intermediate'), str_contains($lower, 'fortgeschritten') => 'intermediate',
            str_contains($lower, 'advanced'), str_contains($lower, 'experte') => 'advanced',
            default => $value,
        };
    }

    /** Mirrors TripDataProcessor::normalizeMonthOption(). */
    private function normalizeMonthOption(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $months = [
            'januar' => '01', 'january' => '01',
            'februar' => '02', 'february' => '02',
            'märz' => '03', 'maerz' => '03', 'march' => '03',
            'april' => '04',
            'mai' => '05', 'may' => '05',
            'juni' => '06', 'june' => '06',
            'juli' => '07', 'july' => '07',
            'august' => '08',
            'september' => '09',
            'oktober' => '10', 'october' => '10',
            'november' => '11',
            'dezember' => '12', 'december' => '12',
        ];

        $firstWord = Str::lower(rtrim(explode(' ', trim($value))[0], '.,;'));

        if (isset($months[$firstWord])) {
            return $months[$firstWord];
        }

        $num = (int) preg_replace('/\D/', '', (string) $value);
        if ($num >= 1 && $num <= 12) {
            return str_pad((string) $num, 2, '0', STR_PAD_LEFT);
        }

        return null;
    }

    private function toInt(string $value): ?int
    {
        if ($value === '' || $this->isPlaceholder($value)) {
            return null;
        }

        return preg_match('/(\d+)/', $value, $match) ? (int) $match[1] : null;
    }

    private function toDecimal(string $value): ?string
    {
        if ($value === '' || $this->isPlaceholder($value)) {
            return null;
        }

        if (preg_match('/(\d+(?:[.,]\d+)?)/', str_replace([' ', '€'], '', $value), $match)) {
            return str_replace(',', '.', $match[1]);
        }

        return null;
    }

    private function cellText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return trim((string) $value);
    }

    /**
     * @return array<int, array{departure_date: string, spots_available: int}>
     */
    public function readAvailabilityFromFile(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $rows = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (! str_contains(Str::lower($sheet->getTitle()), 'verf')) {
                continue;
            }

            $pendingDate = null;

            for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
                $label = $this->cellText($sheet->getCell('A' . $row)->getCalculatedValue());
                $value = $this->cellText($sheet->getCell('B' . $row)->getCalculatedValue());

                if ($label === 'Anreisedatum' && $value !== '') {
                    $pendingDate = $value;
                    continue;
                }

                if ($label === 'Freie Plätze' && $pendingDate !== null) {
                    $parsedDate = $this->parseDepartureDate($pendingDate);
                    if ($parsedDate !== null) {
                        $rows[] = [
                            'departure_date'  => $parsedDate,
                            'spots_available' => $this->toInt($value) ?? 0,
                        ];
                    }
                    $pendingDate = null;
                }
            }
        }

        return $rows;
    }

    private function parseDepartureDate(string $value): ?string
    {
        if ($this->isPlaceholder($value)) {
            return null;
        }

        $value = trim(explode('–', $value)[0]);
        $value = trim(explode('-', $value)[0]);

        foreach (['d.m.Y', 'd.m.y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
