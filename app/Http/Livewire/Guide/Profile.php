<?php

namespace App\Http\Livewire\Guide;

use App\Models\GuideTag;
use App\Models\User;
use App\Rules\Iban;
use App\Services\Guide\Area\GuideCertificateService;
use App\Services\Guide\Area\GuideLanguageCatalog;
use App\Services\Guide\Area\GuideProfileFormService;
use App\Services\Guide\Area\GuideTagService;
use App\Services\Media\GuideProfilePhotoUploadService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Guide Area › Guide profile (/guide/profile). Thin: loading/saving lives in
 * GuideProfileFormService, certificates in GuideCertificateService, photos in
 * GuideProfilePhotoUploadService. The guide is always auth()->user(); no ids from the client
 * except certificate ids, which are looked up through the guide's own relation.
 */
#[Layout('layouts.guide-area')]
class Profile extends Component
{
    use WithFileUploads;

    // Fishing profile
    public string $displayName = '';
    public array $languages = [];
    public string $aboutMe = '';
    public string $favoriteFish = '';
    public string $fishingSince = '';
    public string $crewSize = '';
    public string $foundedYear = '';
    public array $specialties = [];
    public array $goodFor = [];

    // Payment methods
    public bool $barAllowed = false;
    public bool $bankAllowed = false;
    public bool $paypalAllowed = false;
    public string $bankHolder = '';
    public string $bankIban = '';
    public string $bankBic = '';
    public string $paypalDetails = '';
    public string $legacyBankDetails = '';

    // Photo (uploaded as soon as it is picked)
    public $photo = null;

    // Certificate upload panel
    public bool $certificateFormOpen = false;
    public ?int $replaceCertificateId = null;
    public string $certificateType = '';
    public string $certificateTitle = '';
    public string $certificateValidUntil = '';
    public $certificateFile = null;

    public function mount(GuideProfileFormService $form): void
    {
        $this->fill($form->state($this->guide()));
    }

    public function save(GuideProfileFormService $form): void
    {
        $data = $this->validate($this->profileRules($form));

        if (! $this->barAllowed && ! $this->bankAllowed && ! $this->paypalAllowed) {
            $this->addError('paymentMethods', __('guide_area.validation.payment_method_required'));

            return;
        }

        $form->save($this->guide(), $data);
        $this->legacyBankDetails = filled($data['bankIban'] ?? null) ? '' : $this->legacyBankDetails;
        if (filled($this->bankIban)) {
            $this->bankIban = Iban::format($this->bankIban);
        }

        $this->dispatch('guide-area-toast', message: __('guide_area.profile.saved'));
    }

    public function updatedPhoto(GuideProfilePhotoUploadService $photos): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('guide_area.photo.max_kb', 8192), 'dimensions:min_width=200,min_height=200'],
        ]);

        $photos->store($this->guide(), $this->photo);
        $this->reset('photo');
        $this->dispatch('guide-area-toast', message: __('guide_area.profile.photo_saved'));
    }

    public function removePhoto(GuideProfilePhotoUploadService $photos): void
    {
        $photos->remove($this->guide());
        $this->dispatch('guide-area-toast', message: __('guide_area.profile.photo_removed'));
    }

    public function openCertificateForm(string $type = '', ?int $replaceId = null): void
    {
        $this->resetErrorBag();
        $this->reset('certificateTitle', 'certificateValidUntil', 'certificateFile');

        if ($replaceId !== null) {
            $certificate = $this->guide()->guideCertificates()->findOrFail($replaceId);
            $this->authorize('update', $certificate);
            $type = $certificate->type;
            $this->certificateTitle = (string) $certificate->title;
            $this->certificateValidUntil = (string) $certificate->valid_until?->toDateString();
        }

        $this->replaceCertificateId = $replaceId;
        $this->certificateType = $type;
        $this->certificateFormOpen = true;
    }

    public function closeCertificateForm(): void
    {
        $this->certificateFormOpen = false;
        $this->reset('replaceCertificateId', 'certificateType', 'certificateTitle', 'certificateValidUntil', 'certificateFile');
    }

    public function uploadCertificate(GuideCertificateService $certificates): void
    {
        $data = $this->validate([
            'certificateType' => ['required', Rule::in($certificates->types())],
            'certificateTitle' => ['nullable', 'required_if:certificateType,other', 'string', 'max:120'],
            'certificateValidUntil' => ['nullable', 'date', 'after:today'],
            'certificateFile' => ['required', 'file', 'mimes:'.implode(',', (array) config('guide_area.certificates.mimes')), 'max:'.(int) config('guide_area.certificates.max_kb', 10240)],
        ]);

        $guide = $this->guide();
        $replace = null;
        if ($this->replaceCertificateId !== null) {
            $replace = $guide->guideCertificates()->findOrFail($this->replaceCertificateId);
            $this->authorize('update', $replace);
        }

        $certificates->upload(
            $guide,
            $data['certificateType'],
            $data['certificateTitle'] ?: null,
            $this->certificateFile,
            $data['certificateValidUntil'] ?: null,
            $replace,
        );

        $this->closeCertificateForm();
        $this->dispatch('guide-area-toast', message: __('guide_area.certificates.uploaded'));
    }

    public function render(
        GuideLanguageCatalog $languageCatalog,
        GuideTagService $tags,
        GuideCertificateService $certificates,
        GuideProfileFormService $form,
    ) {
        $guide = $this->guide();

        return view('livewire.guide.profile', [
            'guide' => $guide,
            'isCompany' => $form->isCompany($guide),
            'aboutMeMax' => $form->aboutMeMax($guide),
            'languageOptions' => $languageCatalog->options(),
            'languageCatalog' => $languageCatalog,
            'tagService' => $tags,
            'specialtySuggestions' => $tags->suggestions(GuideTag::KIND_SPECIALTY),
            'goodForSuggestions' => $tags->suggestions(GuideTag::KIND_GOOD_FOR),
            'certificateRows' => $certificates->rows($guide),
            'certificateTypes' => $certificates->types(),
            'photoUrl' => filled($guide->profil_image) ? guide_profile_photo_url($guide) : null,
        ])->title(__('guide_area.profile.title'));
    }

    /** @return array<string, mixed> */
    private function profileRules(GuideProfileFormService $form): array
    {
        $tagMax = (int) config('guide_area.profile.tags_max_per_kind', 15);
        $tagLength = (int) config('guide_area.profile.tag_max_length', 40);
        $isCompany = $form->isCompany($this->guide());

        return [
            'displayName' => ['required', 'string', 'max:'.(int) config('guide_area.profile.display_name_max', 60)],
            'languages' => ['required', 'array', 'min:1', 'max:12'],
            'languages.*' => ['string', 'max:40'],
            'aboutMe' => ['required', 'string', 'min:20', 'max:'.$form->aboutMeMax($this->guide())],
            'favoriteFish' => ['required', 'string', 'max:255'],
            'fishingSince' => ['required', 'integer', 'min:1940', 'max:'.date('Y')],
            'crewSize' => [$isCompany ? 'nullable' : 'prohibited', 'integer', 'min:1', 'max:500'],
            'foundedYear' => [$isCompany ? 'nullable' : 'prohibited', 'integer', 'min:1900', 'max:'.date('Y')],
            'specialties' => ['array', 'max:'.$tagMax],
            'specialties.*' => ['string', 'max:'.$tagLength],
            'goodFor' => ['array', 'max:'.$tagMax],
            'goodFor.*' => ['string', 'max:'.$tagLength],
            'barAllowed' => ['boolean'],
            'bankAllowed' => ['boolean'],
            'paypalAllowed' => ['boolean'],
            'bankHolder' => ['required_if:bankAllowed,true', 'nullable', 'string', 'max:120'],
            'bankIban' => ['required_if:bankAllowed,true', 'nullable', 'string', 'max:42', new Iban],
            'bankBic' => ['nullable', 'string', 'regex:/^[A-Za-z]{6}[A-Za-z0-9]{2}([A-Za-z0-9]{3})?$/'],
            'paypalDetails' => ['required_if:paypalAllowed,true', 'nullable', 'string', 'max:255'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'displayName' => __('guide_area.profile.display_name'),
            'languages' => __('guide_area.profile.languages'),
            'aboutMe' => __('guide_area.profile.about_me'),
            'favoriteFish' => __('guide_area.profile.favorite_fish'),
            'fishingSince' => __('guide_area.profile.fishing_since'),
            'crewSize' => __('guide_area.profile.crew_size'),
            'foundedYear' => __('guide_area.profile.founded_year'),
            'bankHolder' => __('guide_area.payments.account_holder'),
            'bankIban' => __('guide_area.payments.iban'),
            'bankBic' => __('guide_area.payments.bic'),
            'paypalDetails' => __('guide_area.payments.paypal_details'),
            'certificateType' => __('guide_area.certificates.type'),
            'certificateTitle' => __('guide_area.certificates.custom_title'),
            'certificateValidUntil' => __('guide_area.certificates.valid_until'),
            'certificateFile' => __('guide_area.certificates.file'),
            'photo' => __('guide_area.profile.photo'),
        ];
    }

    private function guide(): User
    {
        /** @var User $user */
        $user = auth('web')->user();

        return $user->loadMissing('information');
    }
}
