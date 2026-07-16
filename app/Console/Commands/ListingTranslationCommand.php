<?php

namespace App\Console\Commands;

use App\Services\Translation\ListingTranslationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ListingTranslationCommand extends Command
{
  protected $signature = 'listing:translate
                            {--type=* : Listing types: camp, trip, rental_boat, special_offer, accommodation (default: all)}
                            {--camp=* : Specific camp IDs}
                            {--trip=* : Specific trip IDs}
                            {--rental-boat=* : Specific rental boat IDs}
                            {--special-offer=* : Specific special offer IDs}
                            {--accommodation=* : Specific accommodation IDs}
                            {--language=* : Target languages (e.g. en,de)}
                            {--from= : Source language (default: de)}
                            {--force : Force retranslation even if translations exist}
                            {--recent-only : Only process listings updated in the last 7 days}
                            {--missing-only : Only process listings missing at least one target translation}
                            {--needs-update : Only process listings missing, outdated, or structurally incomplete translations (recommended)}
                            {--report-missing : List listings needing translation work without translating}
                            {--dry-run : Show what would be translated without writing}';

  protected $description = 'Translate camps, trips, rental boats, special offers, and accommodations into the languages table (defaults: EN and DE). Use --needs-update to only translate missing/outdated/incomplete rows without --force.';

  public function __construct(private ListingTranslationService $translationService)
  {
    parent::__construct();
  }

  public function handle(): int
  {
    $this->info('Starting listing translation...');
    $this->newLine();

    $targetLanguages = $this->getTargetLanguages();
    $fromLanguage = $this->option('from') ?: ListingTranslationService::defaultSourceLanguage();
    $types = $this->getTypesToProcess();

    if ($types === []) {
      $this->error('No valid listing types selected.');

      return 1;
    }

    if ($this->option('report-missing')) {
      $this->reportMissingTranslations($types, $targetLanguages);

      return 0;
    }

    $jobs = $this->buildTranslationJobs($types, $targetLanguages);

    if ($jobs->isEmpty()) {
      $this->warn('No listings found to process.');

      return 0;
    }

    $dryRun = (bool) $this->option('dry-run');
    $force = (bool) $this->option('force');

    $this->info('Types: '.implode(', ', $types));
    $this->info('Source language: '.$fromLanguage);
    $this->info('Target languages: '.implode(', ', $targetLanguages));
    $this->info("Jobs: {$jobs->count()}");
    $this->newLine();

    $progressBar = $this->output->createProgressBar($jobs->count());
    $progressBar->start();

    $results = [
      'translated' => 0,
      'skipped' => 0,
      'failed' => 0,
    ];

    foreach ($jobs as $job) {
      $progressBar->advance();

      /** @var Model $listing */
      $listing = $job['listing'];
      $listingType = $job['type'];
      $targetLanguage = $job['language'];

      try {
        if ($fromLanguage === $targetLanguage) {
          $results['skipped']++;
          continue;
        }

        if (! $force && ! $this->translationService->needsTranslationUpdate($listing, $listingType, $targetLanguage, $fromLanguage)) {
          $results['skipped']++;
          continue;
        }

        if ($dryRun) {
          $reasons = implode(', ', $this->translationService->getTranslationUpdateReasons(
            $listing,
            $listingType,
            $targetLanguage,
            $fromLanguage
          ));
          $reasonLabel = $reasons !== '' ? " [{$reasons}]" : '';
          $this->line("\nWould translate [{$listingType}] {$listing->title} (ID: {$listing->id}) → {$targetLanguage}{$reasonLabel}");
          $results['translated']++;
          continue;
        }

        $success = $this->translationService->translateListing(
          $listing,
          $listingType,
          $targetLanguage,
          $fromLanguage,
          $force
        );

        if ($success) {
          $results['translated']++;
        } else {
          $results['failed']++;
        }
      } catch (\Throwable $e) {
        $results['failed']++;
        $this->error("\nFailed [{$listingType}] ID {$listing->id} → {$targetLanguage}: {$e->getMessage()}");
      }
    }

    $progressBar->finish();
    $this->newLine(2);
    $this->displayResults($results, $dryRun);

    return $results['failed'] > 0 ? 1 : 0;
  }

  /**
   * @return array<int, string>
   */
  private function getTargetLanguages(): array
  {
    $languages = $this->option('language');

    if ($languages === [] || $languages === null) {
      $this->info('No languages specified, using defaults: EN, DE');

      return ListingTranslationService::defaultTargetLanguages();
    }

    $result = [];
    foreach ($languages as $language) {
      $result = array_merge($result, explode(',', (string) $language));
    }

    return array_values(array_unique(array_filter(array_map('trim', $result))));
  }

  /**
   * @return array<int, string>
   */
  private function getTypesToProcess(): array
  {
    $requested = $this->option('type');

    if ($requested === [] || $requested === null) {
      return array_keys(ListingTranslationService::LISTING_TYPES);
    }

    $types = [];
    foreach ($requested as $value) {
      foreach (explode(',', (string) $value) as $type) {
        $type = trim($type);
        if ($type !== '' && isset(ListingTranslationService::LISTING_TYPES[$type])) {
          $types[] = $type;
        }
      }
    }

    return array_values(array_unique($types));
  }

  /**
   * @param  array<int, string>  $types
   * @param  array<int, string>  $targetLanguages
   */
  private function buildTranslationJobs(array $types, array $targetLanguages): Collection
  {
    $jobs = collect();

    foreach ($types as $type) {
      $listings = $this->getListingsForType($type, $targetLanguages);

      foreach ($listings as $listing) {
        foreach ($targetLanguages as $targetLanguage) {
          $jobs->push([
            'type' => $type,
            'listing' => $listing,
            'language' => $targetLanguage,
          ]);
        }
      }
    }

    return $jobs;
  }

  /**
   * @param  array<int, string>  $targetLanguages
   * @return Collection<int, Model>
   */
  private function getListingsForType(string $type, array $targetLanguages): Collection
  {
    $config = $this->translationService->configFor($type);
    $ids = $this->getIdsForType($type);

    $query = $config['model']::query()->where('status', $config['status']);

    if ($ids !== []) {
      $query->whereIn('id', $ids);
    } elseif ($this->option('recent-only')) {
      $recentIds = $this->translationService->getListingsNeedingTranslation($type);
      if ($recentIds === []) {
        return collect();
      }
      $query->whereIn('id', $recentIds);
    } elseif ($this->option('missing-only')) {
      return $this->translationService->getListingsMissingTranslations($type, $targetLanguages);
    } elseif ($this->option('needs-update')) {
      return $this->translationService->getListingsNeedingTranslationUpdate(
        $type,
        $targetLanguages,
        $this->option('from') ?: ListingTranslationService::defaultSourceLanguage()
      );
    }

    return $query->get();
  }

  /**
   * @return array<int, int>
   */
  private function getIdsForType(string $type): array
  {
    $optionMap = [
      ListingTranslationService::TYPE_CAMP => 'camp',
      ListingTranslationService::TYPE_TRIP => 'trip',
      ListingTranslationService::TYPE_RENTAL_BOAT => 'rental-boat',
      ListingTranslationService::TYPE_SPECIAL_OFFER => 'special-offer',
      ListingTranslationService::TYPE_ACCOMMODATION => 'accommodation',
    ];

    $option = $optionMap[$type] ?? null;
    if ($option === null) {
      return [];
    }

    $raw = $this->option($option);
    if ($raw === [] || $raw === null) {
      return [];
    }

    $ids = [];
    foreach ($raw as $value) {
      foreach (explode(',', (string) $value) as $id) {
        $id = (int) trim($id);
        if ($id > 0) {
          $ids[] = $id;
        }
      }
    }

    return array_values(array_unique($ids));
  }

  /**
   * @param  array<int, string>  $types
   * @param  array<int, string>  $targetLanguages
   */
  private function reportMissingTranslations(array $types, array $targetLanguages): void
  {
    $fromLanguage = $this->option('from') ?: ListingTranslationService::defaultSourceLanguage();
    $rows = [];

    foreach ($types as $type) {
      $listings = $this->translationService->getListingsNeedingTranslationUpdate($type, $targetLanguages, $fromLanguage);

      foreach ($listings as $listing) {
        $reasonsByLanguage = [];
        foreach ($targetLanguages as $language) {
          if ($fromLanguage === $language) {
            continue;
          }

          $reasons = $this->translationService->getTranslationUpdateReasons($listing, $type, $language, $fromLanguage);
          if ($reasons !== []) {
            $reasonsByLanguage[] = $language.':'.implode('+', $reasons);
          }
        }

        if ($reasonsByLanguage === []) {
          continue;
        }

        $rows[] = [
          $type,
          $listing->id,
          Str::limit($listing->title ?? '-', 50),
          implode('; ', $reasonsByLanguage),
        ];
      }
    }

    if ($rows === []) {
      $this->info('All active listings are up to date for: '.implode(', ', $targetLanguages));

      return;
    }

    $this->table(['Type', 'ID', 'Title', 'Needs update (lang:reason)'], $rows);
    $this->newLine();
    $this->info('Total: '.count($rows).' listing(s) needing translation work.');
    $this->info('Reasons: missing = no row; outdated = source changed; incomplete = broken/partial stored translation.');
    $this->info('Run: php artisan listing:translate --needs-update --type=trip --language=en to translate only these.');
  }

  private function displayResults(array $results, bool $dryRun): void
  {
    $this->info('Translation results:');
    $this->table(
      ['Status', 'Count'],
      [
        [$dryRun ? 'Would translate' : 'Translated', $results['translated']],
        ['Skipped', $results['skipped']],
        ['Failed', $results['failed']],
      ]
    );
  }
}
