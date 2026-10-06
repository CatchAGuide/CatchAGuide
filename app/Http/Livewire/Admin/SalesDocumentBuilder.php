<?php

namespace App\Http\Livewire\Admin;

use App\Enums\Sales\SalesDocumentOutput;
use App\Enums\Sales\SalesDocumentStatus;
use App\Enums\TourExtraUnit;
use App\Models\Employee;
use App\Models\SalesDocument;
use App\Models\User;
use App\Presenters\Sales\SalesDocumentPresenter;
use App\Services\Sales\SalesCatalog;
use App\Services\Sales\SalesDocumentCalculator;
use App\Services\Sales\SalesDocumentChecks;
use App\Services\Sales\SalesDocumentSender;
use App\Services\Sales\SalesDocumentStateMapper;
use App\Services\Sales\SalesDocumentWriter;
use App\Services\Sales\SalesFormat;
use App\Services\Sales\SalesLinks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Traits\Localizable;
use Livewire\Component;
use Throwable;

/**
 * Offer & booking confirmation builder (Admin › Sales › Offers): recipient and settings,
 * product cards, details for the customer, totals bar with the send actions, and a live
 * preview of the customer page / email in offer or confirmation mode.
 *
 * The state is plain arrays (header + cards, see SalesDocumentCalculator); prices come from
 * the product snapshots taken when a product is picked, so typing never queries listings.
 */
class SalesDocumentBuilder extends Component
{
    use Localizable;

    public ?int $documentId = null;

    /** @var array<string, mixed> */
    public array $header = [];

    /** @var list<array<string, mixed>> */
    public array $cards = [];

    public string $previewView = 'page';

    public string $previewOutput = 'offer';

    public string $customerSearch = '';

    /** @var list<string> Card keys whose listing prices changed since the snapshot. */
    public array $staleCards = [];

    public ?string $notice = null;

    public string $noticeType = 'success';

    public int $nextKey = 1;

    public function mount(?SalesDocument $document = null): void
    {
        $mapper = app(SalesDocumentStateMapper::class);

        if ($document?->exists) {
            $this->documentId = $document->id;
            $this->header = $mapper->toHeader($document);
            $this->cards = $mapper->toCards($document);
            $this->nextKey = count($this->cards) + 100;
            $this->previewOutput = $document->status === SalesDocumentStatus::Confirmed ? 'confirmation' : 'offer';

            $catalog = app(SalesCatalog::class);
            foreach ($this->cards as $card) {
                if (is_array($card['product'] ?? null) && $catalog->pricesChanged($card['product'])) {
                    $this->staleCards[] = $card['key'];
                }
            }

            if ($document->hasUnseenAcceptance()) {
                $document->forceFill(['acceptance_seen_at' => now()])->saveQuietly();
            }

            return;
        }

        $this->header = $mapper->blankHeader();
    }

    // ---- Recipient -------------------------------------------------------------------------

    public function setRecipientMode(string $mode): void
    {
        $this->header['recipient_mode'] = $mode === 'customer' ? 'customer' : 'contact';
        if ($this->header['recipient_mode'] === 'contact') {
            $this->header['customer_id'] = null;
        }
    }

    public function selectCustomer(int $userId): void
    {
        $user = User::query()->whereNotNull('email')->find($userId, ['id', 'firstname', 'lastname', 'email', 'phone']);
        if ($user === null) {
            return;
        }

        $this->header = array_merge($this->header, [
            'recipient_mode' => 'customer',
            'customer_id' => $user->id,
            'first_name' => (string) $user->firstname,
            'last_name' => (string) $user->lastname,
            'email' => (string) $user->email,
            'phone' => (string) $user->phone,
        ]);
        $this->customerSearch = '';
    }

    public function updatedHeaderLanguage(): void
    {
        $catalog = app(SalesCatalog::class);
        $locale = $this->locale();

        foreach ($this->cards as $index => $card) {
            if (! is_array($card['product'] ?? null)) {
                continue;
            }

            $fresh = $catalog->product($card['type'], (int) $card['product']['id'], $locale);
            if ($fresh !== null) {
                $this->cards[$index]['product'] = self::relocalized($card['product'], $fresh);
            }
        }
    }

    // ---- Cards -----------------------------------------------------------------------------

    public function addCard(string $type): void
    {
        if (! in_array($type, ['tour', 'camp', 'trip', 'custom'], true)) {
            return;
        }

        $card = ['key' => 'c'.$this->nextKey++, 'type' => $type];

        $this->cards[] = $type === 'custom'
            ? $card + ['title' => '', 'description' => '', 'date' => '', 'quantity' => 1, 'unit_label' => '', 'unit_price' => '']
            : $card + ['listing_id' => null, 'product' => null, 'date' => '', 'persons' => $this->defaultPersons(), 'override' => '', 'extras' => [], 'subs' => []];
    }

    public function removeCard(int $index): void
    {
        unset($this->cards[$index]);
        $this->cards = array_values($this->cards);
    }

    public function moveCard(int $index, int $direction): void
    {
        $target = $index + ($direction < 0 ? -1 : 1);
        if (! isset($this->cards[$index], $this->cards[$target])) {
            return;
        }

        [$this->cards[$index], $this->cards[$target]] = [$this->cards[$target], $this->cards[$index]];
    }

    /**
     * Picks the listing of a card. Changing the product resets its extras and camp options.
     */
    public function selectProduct(int $index, int $listingId): void
    {
        $card = $this->cards[$index] ?? null;
        if ($card === null || ($card['listing_id'] ?? null) === $listingId) {
            return;
        }

        $product = app(SalesCatalog::class)->product($card['type'], $listingId, $this->locale());
        if ($product === null) {
            return;
        }

        $this->cards[$index] = array_merge($card, [
            'listing_id' => $listingId,
            'product' => $product,
            'extras' => [],
            'subs' => [],
            'override' => '',
        ]);
        $this->staleCards = array_values(array_diff($this->staleCards, [$card['key']]));
    }

    /**
     * Replaces a card's snapshot with the listing's current data (after a price change hint).
     */
    public function refreshProduct(int $index): void
    {
        $card = $this->cards[$index] ?? null;
        if (! is_array($card['product'] ?? null)) {
            return;
        }

        $fresh = app(SalesCatalog::class)->product($card['type'], (int) $card['product']['id'], $this->locale());
        if ($fresh === null) {
            return;
        }

        $this->cards[$index]['product'] = $fresh;
        $this->cards[$index]['extras'] = array_intersect_key((array) ($card['extras'] ?? []), array_flip(array_column($fresh['extras'] ?? [], 'key')));
        $this->staleCards = array_values(array_diff($this->staleCards, [$card['key']]));
    }

    public function stepPersons(int $index, int $delta): void
    {
        if (isset($this->cards[$index])) {
            $this->cards[$index]['persons'] = max(1, (int) ($this->cards[$index]['persons'] ?? 1) + ($delta < 0 ? -1 : 1));
            $this->syncFollowingExtras($index);
        }
    }

    /**
     * Per-person extras that were never edited take the tour's person count.
     */
    private function syncFollowingExtras(int $index): void
    {
        foreach ((array) ($this->cards[$index]['extras'] ?? []) as $key => $choice) {
            if ($choice['follow'] ?? false) {
                $this->cards[$index]['extras'][$key]['qty'] = max(1, (int) $this->cards[$index]['persons']);
            }
        }
    }

    public function toggleExtra(int $index, string $extraKey): void
    {
        $card = $this->cards[$index] ?? null;
        $extra = collect($card['product']['extras'] ?? [])->firstWhere('key', $extraKey);
        if ($extra === null) {
            return;
        }

        if (isset($card['extras'][$extraKey])) {
            unset($this->cards[$index]['extras'][$extraKey]);

            return;
        }

        $perPerson = TourExtraUnit::fromListing($extra['unit']) === TourExtraUnit::PerPerson;
        $this->cards[$index]['extras'][$extraKey] = [
            'qty' => $perPerson ? (int) $card['persons'] : 1,
            'follow' => $perPerson,
        ];
    }

    /**
     * A per-person extra follows the tour's person count until its count is edited.
     */
    public function updated(string $property): void
    {
        if (preg_match('/^cards\.(\d+)\.extras\.([^.]+)\.qty$/', $property, $match)) {
            $this->cards[(int) $match[1]]['extras'][$match[2]]['follow'] = false;
        }

        if (preg_match('/^cards\.(\d+)\.persons$/', $property, $match)) {
            $this->syncFollowingExtras((int) $match[1]);
        }
    }

    public function addSub(int $index, string $kind): void
    {
        $card = $this->cards[$index] ?? null;
        $options = match ($kind) {
            'accommodation' => $card['product']['accommodations'] ?? [],
            'boat' => $card['product']['boats'] ?? [],
            'guiding' => $card['product']['guidings'] ?? [],
            default => null,
        };
        if ($card === null || $options === null || $options === []) {
            return;
        }

        $stays = array_filter((array) ($card['subs'] ?? []), fn (array $sub) => $sub['kind'] === 'accommodation');
        $firstStay = reset($stays) ?: [];

        $this->cards[$index]['subs'][] = [
            'key' => 's'.$this->nextKey++,
            'kind' => $kind,
            'option_id' => (int) array_key_first($options),
            'from' => $kind === 'accommodation' ? (string) ($firstStay['to'] ?? '') : '',
            'to' => '',
            'date' => $kind === 'guiding' ? (string) ($firstStay['from'] ?? '') : '',
            'days' => $kind === 'boat' ? $this->stayDays($stays) : 1,
            'qty' => 1,
            'override' => '',
        ];
    }

    public function removeSub(int $index, int $subIndex): void
    {
        unset($this->cards[$index]['subs'][$subIndex]);
        $this->cards[$index]['subs'] = array_values($this->cards[$index]['subs'] ?? []);
    }

    public function selectOption(int $index, int $subIndex, int $optionId): void
    {
        if (isset($this->cards[$index]['subs'][$subIndex])) {
            $this->cards[$index]['subs'][$subIndex]['option_id'] = $optionId;
        }
    }

    // ---- Details ---------------------------------------------------------------------------

    public function addNotIncluded(): void
    {
        $this->header['not_included'][] = '';
        $this->dispatch('sales-focus', selector: '[data-not-included="'.(count($this->header['not_included']) - 1).'"]');
    }

    public function removeNotIncluded(int $index): void
    {
        unset($this->header['not_included'][$index]);
        $this->header['not_included'] = array_values($this->header['not_included']);
    }

    // ---- Preview & actions -----------------------------------------------------------------

    // Separate setters: one call per tab click, so quick consecutive clicks don't overwrite each other.
    public function setPreviewView(string $view): void
    {
        $this->previewView = $view === 'mail' ? 'mail' : 'page';
    }

    public function setPreviewOutput(string $output): void
    {
        $this->previewOutput = $output === 'confirmation' ? 'confirmation' : 'offer';
    }

    public function saveDraft(): void
    {
        $this->persist();
        $this->flash('success', __('sales.builder.saved'));
    }

    public function sendOffer(): void
    {
        $this->send(SalesDocumentOutput::Offer);
    }

    public function sendConfirmation(): void
    {
        $this->send(SalesDocumentOutput::Confirmation);
    }

    private function send(SalesDocumentOutput $output): void
    {
        $quote = app(SalesDocumentCalculator::class)->calculate($this->cards, $this->locale());
        $checks = app(SalesDocumentChecks::class)->check($this->header, $quote);
        if ($checks['errors'] !== []) {
            $this->flash('danger', trans_choice('sales.builder.blocked', count($checks['errors']), ['count' => count($checks['errors'])]));

            return;
        }

        $document = $this->persist();

        try {
            app(SalesDocumentSender::class)->send($document, $output, $this->employee());
        } catch (Throwable $exception) {
            report($exception);
            $this->flash('danger', __('sales.builder.send_failed', ['message' => $exception->getMessage()]));

            return;
        }

        $this->setPreviewView('mail');
        $this->setPreviewOutput($output->value);
        $this->flash('success', __($output === SalesDocumentOutput::Offer ? 'sales.builder.offer_sent' : 'sales.builder.confirmation_sent', ['email' => $document->email]));
    }

    private function persist(): SalesDocument
    {
        $document = $this->documentId ? SalesDocument::findOrFail($this->documentId) : new SalesDocument;
        $isNew = ! $document->exists;

        $document = app(SalesDocumentWriter::class)->save($document, $this->header, $this->cards, $this->employee());
        $this->documentId = $document->id;

        if ($isNew) {
            $this->js('history.replaceState({}, "", '.json_encode(route('admin.sales.offers.edit', $document)).')');
        }

        return $document;
    }

    private function flash(string $type, string $message): void
    {
        $this->noticeType = $type;
        $this->notice = $message;
    }

    public function render()
    {
        $locale = $this->locale();
        $document = $this->documentId ? SalesDocument::with('creator')->find($this->documentId) : null;
        $output = SalesDocumentOutput::from($this->previewOutput);
        $creator = $document?->creator?->name ?? $this->employee()?->name;

        $presenter = app(SalesDocumentPresenter::class);
        $links = app(SalesLinks::class);
        $doc = $presenter->present($this->header, $this->cards, $output, [
            'status' => $document?->status ?? SalesDocumentStatus::Draft,
            'number' => (string) $document?->number,
            'token' => $document?->public_token,
            'url' => $document ? $links->customerUrl($document) : null,
            'creator' => $creator,
        ]);
        $quote = $doc['quote'];
        $checks = app(SalesDocumentChecks::class)->check($this->header, $quote);
        $shell = $links->shell($locale);

        $previewHtml = $this->withLocale($locale, fn () => $this->previewView === 'mail'
            ? view('mails.sales.document', ['doc' => $doc] + $shell)->render()
            : view('sales.offer', ['doc' => $doc, 'preview' => true, 'acceptAction' => null] + $shell)->render());

        $groupsByKey = [];
        foreach ($quote->groups as $group) {
            $groupsByKey[$group['key']] = $group;
        }

        return view('livewire.admin.sales-document-builder', [
            'document' => $document,
            'events' => $document?->events()->with('employee:id,name')->limit(30)->get() ?? collect(),
            'sourceRequest' => $document?->sourceRequest(),
            'doc' => $doc,
            'quote' => $quote,
            'groups' => $groupsByKey,
            'checks' => $checks,
            'previewHtml' => $previewHtml,
            'previewUrl' => $doc['url'] ?? $links->absolute('/offer/…', $locale),
            'mailRecipients' => $this->mailRecipients($quote->partners(), $document, $output),
            'customerResults' => $this->customerResults(),
            'periodLabel' => $quote->travelFrom
                ? SalesFormat::date($quote->travelFrom, app()->getLocale()).' – '.SalesFormat::date($quote->travelTo, app()->getLocale())
                : null,
            'introPlaceholder' => __('sales.customer.'.($output === SalesDocumentOutput::Offer ? 'intro_offer' : 'intro_confirmation'), [], $locale),
        ]);
    }

    /**
     * @param  list<array{email: string}>  $partners
     * @return array{to: string, cc: string, bcc: string}
     */
    private function mailRecipients(array $partners, ?SalesDocument $document, SalesDocumentOutput $output): array
    {
        $creator = $document?->creator?->email ?? $this->employee()?->email;
        $name = trim(($this->header['first_name'] ?? '').' '.($this->header['last_name'] ?? ''));
        $to = trim($name.' <'.($this->header['email'] ?? '').'>');

        return $output === SalesDocumentOutput::Offer
            ? ['to' => $to, 'cc' => '', 'bcc' => (string) $creator]
            : ['to' => $to, 'cc' => implode(', ', array_filter([...array_column($partners, 'email'), $creator])), 'bcc' => ''];
    }

    /**
     * @return list<array{id: int, label: string}>
     */
    private function customerResults(): array
    {
        $term = trim($this->customerSearch);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return User::query()
            ->whereNotNull('email')
            ->where(fn ($query) => $query->where('email', 'like', $like)->orWhere('firstname', 'like', $like)->orWhere('lastname', 'like', $like))
            ->orderBy('firstname')
            ->limit(8)
            ->get(['id', 'firstname', 'lastname', 'email'])
            ->map(fn (User $user) => ['id' => $user->id, 'label' => trim($user->firstname.' '.$user->lastname).' · '.$user->email])
            ->all();
    }

    private function locale(): string
    {
        return ($this->header['language'] ?? 'de') === 'en' ? 'en' : 'de';
    }

    private function employee(): ?Employee
    {
        $user = Auth::guard('employees')->user();

        return $user instanceof Employee ? $user : null;
    }

    /**
     * Persons for a new card: the recipient plus the further travellers.
     */
    private function defaultPersons(): int
    {
        $names = array_filter([trim((string) ($this->header['first_name'] ?? '')), ...SalesDocumentStateMapper::travellers((string) ($this->header['travellers'] ?? ''))]);

        return max(1, count($names));
    }

    /**
     * Length of the stay in days (nights + 1) across the camp's accommodations; 1 without dates.
     *
     * @param  array<int, array<string, mixed>>  $stays
     */
    private function stayDays(array $stays): int
    {
        $days = 1;
        foreach ($stays as $stay) {
            $from = SalesFormat::parse($stay['from'] ?? null);
            $to = SalesFormat::parse($stay['to'] ?? null);
            if ($from !== null && $to !== null && $to->gt($from)) {
                $days = max($days, (int) $from->diffInDays($to) + 1);
            }
        }

        return $days;
    }

    /**
     * Texts of a fresh snapshot in the new language over the stored snapshot's prices.
     *
     * @param  array<string, mixed>  $stored
     * @param  array<string, mixed>  $fresh
     * @return array<string, mixed>
     */
    private static function relocalized(array $stored, array $fresh): array
    {
        foreach (['title', 'location', 'url', 'duration', 'inclusions', 'translation_missing'] as $field) {
            if (array_key_exists($field, $fresh)) {
                $stored[$field] = $fresh[$field];
            }
        }

        $names = array_column($fresh['extras'] ?? [], 'name', 'key');
        foreach ($stored['extras'] ?? [] as $i => $extra) {
            $stored['extras'][$i]['name'] = $names[$extra['key']] ?? $extra['name'];
        }

        foreach (['accommodations', 'boats', 'guidings'] as $group) {
            foreach ($stored[$group] ?? [] as $id => $option) {
                $stored[$group][$id]['name'] = $fresh[$group][$id]['name'] ?? $option['name'];
            }
        }

        return $stored;
    }
}
