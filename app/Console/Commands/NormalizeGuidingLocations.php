<?php

namespace App\Console\Commands;

use App\Models\Guiding;
use App\Services\Location\CountryResolver;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NormalizeGuidingLocations extends Command
{
    protected $signature = 'guidings:normalize-locations
                            {--dry-run : Report changes without writing}
                            {--limit=0 : Max guidings per run (0 = all, default)}
                            {--only-country : Normalize country and country_iso only (fast, no Nominatim)}
                            {--id= : Single guiding id}
                            {--sleep=1 : Seconds between Nominatim geocode calls}';

    protected $description = 'Backfill guiding lat/lng and normalize city/country/region to English (batch, off-peak)';

    public function handle(CountryResolver $countryResolver): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $onlyCountry = (bool) $this->option('only-country');
        $sleepSeconds = max(0, (int) $this->option('sleep'));
        $guidingId = $this->option('id');

        $query = Guiding::query()->orderBy('id');
        if ($guidingId) {
            $query->where('id', $guidingId);
        } elseif ($limit > 0) {
            $query->limit($limit);
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No guidings to process.');

            return self::SUCCESS;
        }

        $scope = $guidingId ? "guiding #{$guidingId}" : ($limit > 0 ? "up to {$limit}" : 'all');
        $this->info(($dryRun ? '[DRY RUN] ' : '')."Processing {$scope} ({$total} row(s))...");
        if (! $onlyCountry && $sleepSeconds > 0) {
            $this->comment("Nominatim: ~{$sleepSeconds}s pause per guiding that needs geocoding (use --only-country to skip).");
        }

        $updated = 0;
        $processed = 0;
        $client = new Client(['timeout' => 15]);

        $process = function (Guiding $guiding) use (
            $countryResolver,
            $client,
            $dryRun,
            $onlyCountry,
            $sleepSeconds,
            &$updated,
            &$processed
        ): void {
            $processed++;
            $changes = $this->buildChangesForGuiding($guiding, $countryResolver, $client, $onlyCountry, $sleepSeconds);

            if ($changes === []) {
                return;
            }

            $this->line("Guiding #{$guiding->id}: ".json_encode($changes));

            if (! $dryRun) {
                $guiding->update($changes);
            }
            $updated++;
        };

        if ($guidingId || $limit > 0) {
            foreach ($query->get() as $guiding) {
                $process($guiding);
            }
        } else {
            $query->chunkById(200, function ($guidings) use ($process) {
                foreach ($guidings as $guiding) {
                    $process($guiding);
                }
            });
        }

        $this->info(($dryRun ? 'Would update' : 'Updated')." {$updated} of {$processed} processed guiding(s).");

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildChangesForGuiding(
        Guiding $guiding,
        CountryResolver $countryResolver,
        Client $client,
        bool $onlyCountry,
        int $sleepSeconds
    ): array {
        $changes = [];
        $iso = $countryResolver->resolveIso(null, $guiding->country);
        if ($iso) {
            $englishCountry = $countryResolver->englishName($iso);
            if ($englishCountry && $guiding->country !== $englishCountry) {
                $changes['country'] = $englishCountry;
            }
            if ($guiding->country_iso !== $iso) {
                $changes['country_iso'] = $iso;
            }
        }

        if ($onlyCountry) {
            return $changes;
        }

        $needsCoords = empty($guiding->lat) || empty($guiding->lng)
            || (float) $guiding->lat == 0.0 || (float) $guiding->lng == 0.0;

        if ($needsCoords && $sleepSeconds > 0) {
            sleep($sleepSeconds);
        }

        if (! $needsCoords) {
            return $changes;
        }

        $coords = $this->geocodeGuiding($client, $guiding);
        if (! $coords) {
            return $changes;
        }

        $changes['lat'] = $coords['lat'];
        $changes['lng'] = $coords['lng'];
        if (! empty($coords['city']) && empty($guiding->city)) {
            $changes['city'] = $coords['city'];
        }
        if (! empty($coords['region']) && empty($guiding->region)) {
            $changes['region'] = $coords['region'];
        }
        if (! empty($coords['country']) && empty($changes['country'])) {
            $resolvedIso = $countryResolver->resolveIso($coords['country_short'] ?? null, $coords['country']);
            if ($resolvedIso) {
                $changes['country'] = $countryResolver->englishName($resolvedIso);
                $changes['country_iso'] = $resolvedIso;
            }
        }

        return $changes;
    }

    /**
     * @return array{lat: float, lng: float, city?: string, region?: string, country?: string, country_short?: string}|null
     */
    private function geocodeGuiding(Client $client, Guiding $guiding): ?array
    {
        $q = implode(', ', array_filter([
            $guiding->location,
            $guiding->city,
            $guiding->region,
            $guiding->country,
        ]));

        if ($q === '') {
            return null;
        }

        try {
            if (! empty($guiding->lat) && ! empty($guiding->lng) && (float) $guiding->lat != 0.0) {
                $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
                    'query' => [
                        'lat' => $guiding->lat,
                        'lon' => $guiding->lng,
                        'format' => 'json',
                        'accept-language' => 'en',
                        'addressdetails' => 1,
                    ],
                    'headers' => [
                        'User-Agent' => 'CAG-Guiding-App/1.0 (normalize-locations)',
                    ],
                ]);
            } else {
                $response = $client->get('https://nominatim.openstreetmap.org/search', [
                    'query' => [
                        'q' => $q,
                        'format' => 'json',
                        'limit' => 1,
                        'accept-language' => 'en',
                        'addressdetails' => 1,
                    ],
                    'headers' => [
                        'User-Agent' => 'CAG-Guiding-App/1.0 (normalize-locations)',
                    ],
                ]);
            }

            $data = json_decode($response->getBody()->getContents(), true);
            if (! $data) {
                return null;
            }

            if (isset($data[0])) {
                $data = $data[0];
            }

            $address = $data['address'] ?? [];

            return [
                'lat' => (float) ($data['lat'] ?? 0),
                'lng' => (float) ($data['lon'] ?? 0),
                'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
                'region' => $address['state'] ?? null,
                'country' => $address['country'] ?? null,
                'country_short' => $address['country_code'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Nominatim geocode failed in normalize-locations', [
                'guiding_id' => $guiding->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
