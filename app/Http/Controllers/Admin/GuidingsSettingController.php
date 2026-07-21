<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use App\Models\Levels;
use App\Models\Inclussion;
use App\Models\FishingType;
use App\Models\FishingFrom;
use App\Models\FishingEquipment;
use App\Models\BoatExtras;
use App\Models\Facility;
use App\Models\KitchenEquipment;
use Illuminate\Http\Request;

class GuidingsSettingController extends Controller
{
    public function equipmentIndex()
    {
        $equipment = FishingEquipment::query()->orderBy('id', 'desc')->get();
        return view('admin.pages.setting.equipment.index', compact('equipment'));
    }

    public function storeequipment(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string',
            'name_en' => 'nullable|string',
        ]);

        FishingEquipment::create($request->only(['name', 'name_en']));

        return back()->with('success', 'Fishing equipment was successfully added');
    }

    public function updatefishingequipment(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string',
            'name_en' => 'nullable|string',
        ]);

        $row = FishingEquipment::findOrFail($id);
        $row->update($request->only(['name', 'name_en']));

        return back()->with('success', 'Fishing equipment was successfully updated');
    }

    public function deleteequipment($id)
    {
        FishingEquipment::findOrFail($id)->delete();
        return back()->with('success', 'Fishing equipment was successfully deleted');
    }

    public function boatExtrasIndex()
    {
        $extras = BoatExtras::query()->orderBy('id', 'desc')->get();
        return view('admin.pages.setting.boat-extras.index', compact('extras'));
    }

    public function storeBoatExtra(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        BoatExtras::create($request->only(['name', 'name_en']));
        return back()->with('success', 'Boat extra was successfully added');
    }

    public function updateBoatExtra(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $row = BoatExtras::findOrFail($id);
        $row->update($request->only(['name', 'name_en']));

        return back()->with('success', 'Boat extra was successfully updated');
    }

    public function deleteBoatExtra($id)
    {
        BoatExtras::findOrFail($id)->delete();
        return back()->with('success', 'Boat extra was successfully deleted');
    }

    public function facilitiesIndex()
    {
        $facilities = Facility::query()->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.pages.setting.facilities.index', compact('facilities'));
    }

    public function storeFacility(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        Facility::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'is_active' => (bool)($request->boolean('is_active', true)),
            'sort_order' => (int)($request->input('sort_order', 0)),
        ]);

        return back()->with('success', 'Facility was successfully added');
    }

    public function updateFacility(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $row = Facility::findOrFail($id);
        $row->update([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'is_active' => (bool)($request->boolean('is_active', true)),
            'sort_order' => (int)($request->input('sort_order', 0)),
        ]);

        return back()->with('success', 'Facility was successfully updated');
    }

    public function deleteFacility($id)
    {
        Facility::findOrFail($id)->delete();
        return back()->with('success', 'Facility was successfully deleted');
    }

    public function kitchenEquipmentIndex()
    {
        $equipment = KitchenEquipment::query()->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.pages.setting.kitchen-equipment.index', compact('equipment'));
    }

    public function storeKitchenEquipment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        KitchenEquipment::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'is_active' => (bool)($request->boolean('is_active', true)),
            'sort_order' => (int)($request->input('sort_order', 0)),
        ]);

        return back()->with('success', 'Kitchen equipment was successfully added');
    }

    public function updateKitchenEquipment(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $row = KitchenEquipment::findOrFail($id);
        $row->update([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'is_active' => (bool)($request->boolean('is_active', true)),
            'sort_order' => (int)($request->input('sort_order', 0)),
        ]);

        return back()->with('success', 'Kitchen equipment was successfully updated');
    }

    public function deleteKitchenEquipment($id)
    {
        KitchenEquipment::findOrFail($id)->delete();
        return back()->with('success', 'Kitchen equipment was successfully deleted');
    }

    public function targetIndex(){
        
        $targets = Target::all();
        return view('admin.pages.setting.targets.index', compact('targets'));
    }

    public function methodIndex(){
        
        $methods = Method::all();
        return view('admin.pages.setting.methods.index', compact('methods'));
    }

    public function waterIndex(){
        
        $waters = Water::all();
        return view('admin.pages.setting.waters.index', compact('waters'));
    }

    public function inclussionIndex(){
        $inclussions = Inclussion::all();
        return view('admin.pages.setting.inclussions.index', compact('inclussions'));
    }

    public function fishingfromIndex(){
        $fishingfroms = FishingFrom::all();
        return view('admin.pages.setting.fishingfrom.index', compact('fishingfroms'));
    }

    public function fishingtypeIndex(){
        $fishingtypes = FishingType::all();
        return view('admin.pages.setting.fishingtype.index', compact('fishingtypes'));
    }

    public function levelIndex(){
        $levels = Levels::all();
        return view('admin.pages.setting.levels.index', compact('levels'));
    }



    public function index()
    {
        $methods = Method::all();
        $targets = Target::all();
        $waters = Water::all();
        $levels  = Levels::all();
        $fishingtype  = FishingType::all();
        $fishingfrom  = FishingFrom::all();

        return view('admin.pages.setting.index', compact('methods', 'targets', 'waters'));
    }

    public function storetarget(Request $request)
    {
        $target = new Target();
        $target->name = mb_convert_encoding($request->name, 'UTF-8', 'auto');
        $target->name_en = $request->name_en;
        $target->save();
        return back()->with('success', 'Der Zielfisch wurde erfolgreich angelegt');
    }

    public function storewater(Request $request)
    {
        $water = new Water();
        $water->name = $request->name;
        $water->name_en = $request->name_en;
        $water->save();
        return back()->with('success', 'Das Gewässer wurde erfolgreich angelegt');
    }

    public function storemethod(Request $request)
    {
        $method = new Method();
        $method->name = $request->name;
        $method->name_en = $request->name_en;
        $method->save();
        return back()->with('success', 'Die Methode wurde erfolgreich angelegt');
    }

    public function updatetarget(Request $request, $id)
    {
        $target = Target::find($id);
        $target->name = $request->name;
        $target->name_en = $request->name_en;
        $target->save();
        return back()->with('success', 'Der Zielfisch wurde erfolgreich geupdatet');
    }

    public function updatewater(Request $request, $id)
    {
        $water = Water::find($id);
        $water->name = $request->name;
        $water->name_en = $request->name_en;
        $water->save();
        return back()->with('success', 'Das Gewässer wurde erfolgreich geupdatet');
    }

    public function updatemethod(Request $request, $id)
    {
        $method = Method::find($id);
        $method->name = $request->name;
        $method->name_en = $request->name_en;
        $method->save();
        return back()->with('success', 'Die Methode wurde erfolgreich geupdatet');
    }

    public function deletetarget($id)
    {
        $target = Target::find($id);
        $target->delete();
        return back()->with('success', 'Der Zielfisch wurde erfolgreich gelöscht');
    }

    public function deletewater($id)
    {
        $water = Water::find($id);
        $water->delete();
        return back()->with('success', 'Das Gewässer wurde erfolgreich gelöscht');
    }

    public function deletemethod($id)
    {
        $method = Method::find($id);
        $method->delete();
        return back()->with('success', 'Die Methode wurde erfolgreich gelöscht');
    }

    //inclussion

    
    public function storeinclussion(Request $request)
    {
        $inclussion = new Inclussion();
        $inclussion->name = $request->name;
        $inclussion->name_en = $request->name_en;
        $inclussion->save();
        return back()->with('success', 'Das '.$inclussion->name.' wurde erfolgreich angelegt');
    }

    public function updateinclussion(Request $request, $id)
    {
        $inclussion = Inclussion::find($id);
        $inclussion->name = $request->name;
        $inclussion->name_en = $request->name_en;
        $inclussion->save();
        return back()->with('success', 'Das '.$inclussion->name.' wurde erfolgreich geupdatet');
    }


    public function deleteinclussion($id)
    {
        $inclussion = Inclussion::find($id);
        $inclussion->delete();
        return back()->with('success', 'Die inbegriffen wurde erfolgreich gelöscht');
    }

    //fishing from

    public function storefishingfrom(Request $request)
    {
        $fishingfrom = new FishingFrom();
        $fishingfrom->name = $request->name;
        $fishingfrom->name_en = $request->name_en;
        $fishingfrom->save();
        return back()->with('success', 'Das '.$fishingfrom->name.' wurde erfolgreich angelegt');
    }

    public function updatefishingfrom(Request $request, $id)
    {
        $fishingfrom = FishingFrom::find($id);
        $fishingfrom->name = $request->name;
        $fishingfrom->name_en = $request->name_en;
        $fishingfrom->save();
        return back()->with('success', 'Das '.$fishingfrom->name.' wurde erfolgreich geupdatet');
    }


    public function deletefishingfrom($id)
    {
        $fishingfrom = FishingFrom::find($id);
        $fishingfrom->delete();
        return back()->with('success', 'Die Angeln von wurde erfolgreich gelöscht');
    }

    //fishing type

    public function storefishingtype(Request $request)
    {
        $fishingtype = new FishingType();
        $fishingtype->name = $request->name;
        $fishingtype->name_en = $request->name_en;
        $fishingtype->save();
        return back()->with('success', 'Das '.$fishingtype->name.' wurde erfolgreich angelegt');
    }

    public function updatefishingtype(Request $request, $id)
    {
        $fishingtype = FishingType::find($id);
        $fishingtype->name = $request->name;
        $fishingtype->name_en = $request->name_en;
        $fishingtype->save();
        return back()->with('success', 'Das '.$fishingtype->name.' wurde erfolgreich geupdatet');
    }


    public function deletefishingtype($id)
    {
        $fishingtype = FishingType::find($id);
        $fishingtype->delete();
        return back()->with('success', 'Die Angel-Art wurde erfolgreich gelöscht');
    }

    //fishing level
    
        
    public function storelevel(Request $request)
    {
        $level = new Levels();
        $level->name = $request->name;
        $level->name_en = $request->name_en;
        $level->save();
        return back()->with('success', 'Das '.$level->name.' wurde erfolgreich angelegt');
    }

    public function updatelevel(Request $request, $id)
    {
        $level = Levels::find($id);
        $level->name = $request->name;
        $level->name_en = $request->name_en;
        $level->save();
        return back()->with('success', 'Das '.$level->name.' wurde erfolgreich geupdatet');
    }


    public function deletelevel($id)
    {
        $level = Levels::find($id);
        $level->delete();
        return back()->with('success', 'Die Ausgelegt für wurde erfolgreich gelöscht');
    }

    public function emailmaintenance()
    {
        $emailTemplates = $this->buildEmailTemplateCatalogue();
        $categories = config('email_templates.categories');
        $triggerTypes = config('email_templates.trigger_types');
        $statuses = config('email_templates.statuses');

        $stats = [
            'total' => count($emailTemplates),
            'active' => collect($emailTemplates)->where('status', 'active')->count(),
            'scheduled' => collect($emailTemplates)->where('trigger_type', 'scheduled')->count(),
            'immediate' => collect($emailTemplates)->where('trigger_type', 'immediate')->count(),
        ];

        return view('admin.pages.setting.emails.maintenance', compact(
            'emailTemplates',
            'categories',
            'triggerTypes',
            'statuses',
            'stats'
        ));
    }

    private function buildEmailTemplateCatalogue(): array
    {
        $templates = [];

        foreach (config('email_templates.templates', []) as $key => $meta) {
            $templates[] = array_merge($meta, [
                'template_key' => $key,
                'preview_url_en' => route('admin.settings.email.preview', ['template' => $key, 'locale' => 'en']),
                'preview_url_de' => route('admin.settings.email.preview', ['template' => $key, 'locale' => 'de']),
            ]);
        }

        return $templates;
    }

    public function emailPreview($template, $locale)
    {
        app()->setLocale($locale);

        try {
            $data = $this->getMockEmailData($template);
            $viewPath = $this->getEmailViewPath($template);

            return response(view($viewPath, $data)->render());
        } catch (\Throwable $e) {
            return response($this->renderPreviewErrorHtml($template, $e), 200);
        }
    }

    public function emailPreviewAjax($template, $locale)
    {
        app()->setLocale($locale);

        try {
            $data = $this->getMockEmailData($template);
            $viewPath = $this->getEmailViewPath($template);

            return response()->json([
                'html' => view($viewPath, $data)->render(),
                'template_name' => $this->getTemplateName($template),
                'locale' => $locale,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'html' => $this->renderPreviewErrorHtml($template, $e),
                'template_name' => $this->getTemplateName($template),
                'locale' => $locale,
            ], 200);
        }
    }

    private function renderPreviewErrorHtml(string $template, \Throwable $e): string
    {
        $name = e($this->getTemplateName($template));
        $message = e($e->getMessage());

        return <<<HTML
<!DOCTYPE html>
<html><body style="font-family: Arial, sans-serif; padding: 24px; background: #f8f9fa; color: #313041;">
  <div style="max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #f5c2c7; border-radius: 8px; padding: 20px;">
    <h3 style="margin-top: 0; color: #842029;">Preview unavailable</h3>
    <p style="font-size: 14px;"><strong>{$name}</strong> could not be rendered with sample data.</p>
    <p style="font-size: 12px; color: #6c757d; word-break: break-word;">{$message}</p>
  </div>
</body></html>
HTML;
    }

    private function getMockEmailData($template)
    {
        $mockUser = (object) [
            'id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+49 123 456 7890',
            'phone_country_code' => '+49',
            'language' => 'en',
        ];

        $mockGuide = (object) [
            'id' => 2,
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'email' => 'max.guide@example.com',
            'phone' => '+49 987 654 3210',
            'phone_country_code' => '+49',
            'location' => 'Lake Constance, Germany',
            'language' => 'en',
            'user' => $mockUser,
        ];

        $mockGuiding = (object) [
            'id' => 1,
            'title' => 'Professional Pike Fishing Adventure',
            'location' => 'Lake Constance, Germany',
            'slug' => 'professional-pike-fishing-adventure',
            'user' => $mockGuide,
        ];

        $mockBooking = new class($mockUser, $mockGuiding) {
            public $id = 1;
            public $token = 'mock-booking-token-123';
            public $book_date = '2024-02-15';
            public $count_of_users = 2;
            public $price = 299.99;
            public $cag_percent = 60.00;
            public $phone = '+49 123 456 7890';
            public $email = 'john.doe@example.com';
            public $status = 'pending';
            public $extras;
            public $additional_information = 'Unfortunately, I have to cancel due to weather conditions. However, I can offer you these alternative dates.';
            public $alternativeDates = ['2024-02-20', '2024-02-22', '2024-02-25', '2024-02-26', '2024-02-28'];
            public $textNote = 'Thank you for your booking request. We have received it and are processing it.';
            public $alternativeText = 'Please let us know if any of the alternative dates work for you.';
            public $guideName = 'Max Mustermann';
            public $user;
            public $guiding;

            public function __construct($user, $guiding)
            {
                $this->user = $user;
                $this->guiding = $guiding;
                $this->extras = serialize([
                    [
                        'extra_name' => 'Fishing Equipment',
                        'extra_quantity' => 2,
                        'extra_total_price' => 50.00,
                        'price' => 50.00,
                    ],
                    [
                        'extra_name' => 'Lunch Package',
                        'extra_quantity' => 2,
                        'extra_total_price' => 30.00,
                        'price' => 30.00,
                    ],
                ]);
            }

            public function getFormattedBookingDate($format = 'd.m.Y')
            {
                return date($format, strtotime($this->book_date));
            }

            public function getGrossAmount()
            {
                return $this->price;
            }

            public function getGuideShareAmount()
            {
                return $this->price - $this->cag_percent;
            }
        };

        // Guiding-shaped object used by guide upcoming-tour mail ($guide->user / $guide->location)
        $mockGuidingAsGuide = (object) [
            'id' => $mockGuiding->id,
            'title' => $mockGuiding->title,
            'location' => $mockGuiding->location,
            'slug' => $mockGuiding->slug,
            'user' => $mockGuide,
            'firstname' => $mockGuide->firstname,
            'lastname' => $mockGuide->lastname,
            'email' => $mockGuide->email,
            'phone' => $mockGuide->phone,
            'phone_country_code' => $mockGuide->phone_country_code,
        ];

        $baseData = [
            'user' => $mockUser,
            'guide' => $mockGuide,
            'guiding' => $mockGuiding,
            'booking' => $mockBooking,
        ];

        switch ($template) {
            case 'guest_tour_reminder':
                return array_merge($baseData, [
                    'guestName' => $mockUser->firstname,
                    'guideName' => $mockGuide->firstname,
                    'location' => $mockGuiding->location,
                    'date' => date('F j, Y', strtotime($mockBooking->book_date)),
                ]);

            case 'guest_review':
                return array_merge($baseData, [
                    'userName' => $mockUser->firstname,
                    'guideName' => $mockGuide->firstname,
                    'location' => $mockGuiding->location,
                    'reviewUrl' => url('/review/mock-review-token'),
                ]);

            case 'guest_booking_request':
                $guideName = $mockGuide->firstname;
                $textNote = __('emails.guest_booking_request_text_1');
                $textNote = str_replace('[Guide Name]', $guideName, $textNote);
                $textNote = str_replace('[Date]', date('F j, Y', strtotime($mockBooking->book_date)), $textNote);
                $textNote = str_replace('[Location]', $mockGuiding->location, $textNote);

                $alternativeText = __('emails.guest_booking_request_text_5');
                $alternativeText = str_replace('[Guide Name]', $guideName, $alternativeText);
                $mockBooking->alternativeText = $alternativeText;

                return array_merge($baseData, [
                    'textNote' => $textNote,
                    'alternativeText' => $alternativeText,
                ]);

            case 'guide_upcoming_tour':
                return [
                    'guide' => $mockGuidingAsGuide,
                    'booking' => $mockBooking,
                    'user' => $mockUser,
                    'guiding' => $mockGuiding,
                ];

            case 'guide_review_confirmation':
                return [
                    'name' => $mockGuide->firstname,
                    'guiding_name' => $mockGuiding->title,
                    'score' => 9,
                    'guide_score' => 9,
                    'region_water_score' => 8,
                    'overall_score' => 9,
                    'comment' => 'Amazing experience! The guide was very knowledgeable and patient.',
                ];

            case 'guide_application_approved':
            case 'guide_application_received':
                return ['user' => $mockGuide];

            case 'guide_admin_new_request':
                $mockGuide->guide_type = 'private';
                $mockGuide->information = (object) [
                    'phone' => '+49 987 654 3210',
                    'address' => 'Seestrasse',
                    'address_number' => '12',
                    'postal' => '78462',
                    'city' => 'Konstanz',
                ];

                return ['user' => $mockGuide];

            case 'guide_application_rejected':
                return [
                    'user' => $mockGuide,
                    'rejectionReason' => 'Incomplete profile information. Please add a valid fishing license and more tour photos.',
                ];

            case 'registration_verification':
                return ['user' => $mockUser];

            case 'automatic_registration_mail':
                return [
                    'user' => $mockUser,
                    'tempPassword' => 'TempPass123!',
                ];

            case 'customer_newsletter_mail':
            case 'newsletter':
                return [
                    'email' => $mockUser->email,
                    'language' => 'en',
                    'copyNamespace' => $template === 'newsletter'
                        ? 'emails.newsletter_admin'
                        : 'emails.newsletter_customer',
                    'viewSubscribersUrl' => route('admin.newsletter-subscribers.index'),
                ];

            case 'guide_invoice':
                return $baseData;

            case 'customer_contact_mail':
            case 'contact_mail':
                return [
                    'name' => $mockUser->firstname . ' ' . $mockUser->lastname,
                    'email' => $mockUser->email,
                    'phone' => $mockUser->phone,
                    'phone_country_code' => $mockUser->phone_country_code,
                    'contact_message' => 'Hi — I have a question about guided fishing in Bavaria. Could you help?',
                    'source_title' => 'Bavarian Alpine Trout',
                    'source_type' => 'guiding',
                    'copyNamespace' => $template === 'contact_mail'
                        ? 'emails.contact_admin'
                        : 'emails.contact_customer',
                    'viewRequestsUrl' => route('admin.contact-requests.index'),
                ];

            case 'vacation_booking_customer_mail':
            case 'vacation_booking_admin_mail':
                return [
                    'name' => $mockUser->firstname . ' ' . $mockUser->lastname,
                    'email' => $mockUser->email,
                    'phone' => $mockUser->phone,
                    'phone_country_code' => $mockUser->phone_country_code,
                    'contact_message' => 'Looking forward to this trip — please confirm availability for our group.',
                    'source_title' => 'Lake Constance Pike Fishing',
                    'source_type' => 'trip',
                    'preferred_date' => '2024-08-25',
                    'number_of_persons' => 4,
                    'source_id' => 42,
                    'copyNamespace' => $template === 'vacation_booking_admin_mail'
                        ? 'emails.vacation_booking_admin'
                        : 'emails.vacation_booking_customer',
                    'viewRequestsUrl' => route('admin.trip-bookings.index'),
                ];

            case 'ceo_booking_notification':
            case 'booking_reject_mail_to_ceo':
            case 'booking_cancel':
            case 'ceo_reject_mail':
            case 'ceo_accept_mail':
            case 'ceo_request_mail':
            case 'ceo_expire_mail':
                return $baseData;

            case 'ddos_alert':
                return [
                    'alertType' => 'Rate limit exceeded',
                    'details' => [
                        'ip' => '203.0.113.42',
                        'user_agent' => 'Mozilla/5.0 (compatible; Bot/1.0)',
                        'context' => 'search',
                        'violations' => 25,
                        'url' => 'https://catchaguide.com/guidings',
                    ],
                    'timestamp' => now(),
                ];

            default:
                return $baseData;
        }
    }

    private function getEmailViewPath($template)
    {
        $view = config("email_templates.templates.{$template}.view");

        return $view ?? 'mails.guest.guest_booking_request';
    }

    private function getTemplateName($template)
    {
        return config("email_templates.templates.{$template}.name", 'Unknown Template');
    }
}
