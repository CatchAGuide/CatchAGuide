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
use Illuminate\Http\Request;

class GuidingsSettingController extends Controller
{

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

    public function emailmaintenance(){
        $emailTemplates = [
            [
                'name' => 'Guide Booking Request',
                'description' => 'Email sent to guide when new booking request is received',
                'template_key' => 'guide_booking_request',
                'category' => 'guide'
            ],
            [
                'name' => 'Guide Reminder 24hrs',
                'description' => 'Reminder email sent to guide 24 hours after booking request',
                'template_key' => 'guide_reminder',
                'category' => 'guide'
            ],
            [
                'name' => 'Guide Reminder 12hrs', 
                'description' => 'Reminder email sent to guide 12 hours after booking request',
                'template_key' => 'guide_reminder_12hrs',
                'category' => 'guide'
            ],
            [
                'name' => 'Guide Booking Accepted',
                'description' => 'Email sent to guide when booking is accepted',
                'template_key' => 'guide_accepted_mail',
                'category' => 'guide'
            ],
            [
                'name' => 'Guide Booking Expired',
                'description' => 'Email sent to guide when booking request expires',
                'template_key' => 'guide_expired_booking',
                'category' => 'guide'
            ],
            [
                'name' => 'Guide Upcoming Tour',
                'description' => 'Reminder email sent to guide about upcoming tour',
                'template_key' => 'guide_upcoming_tour',
                'category' => 'guide'
            ],
            [
                'name' => 'Guest Booking Request',
                'description' => 'Email sent to guest when booking request is submitted',
                'template_key' => 'guest_booking_request',
                'category' => 'guest'
            ],
            [
                'name' => 'Guest Booking Accepted',
                'description' => 'Email sent to guest when booking is accepted',
                'template_key' => 'accepted_mail',
                'category' => 'guest'
            ],
            [
                'name' => 'Guest Booking Rejected',
                'description' => 'Email sent to guest when booking is rejected',
                'template_key' => 'rejected_mail',
                'category' => 'guest'
            ],
            [
                'name' => 'Guest Tour Reminder',
                'description' => 'Reminder email sent to guest about upcoming tour',
                'template_key' => 'guest_tour_reminder',
                'category' => 'guest'
            ],
            [
                'name' => 'Guest Review Request',
                'description' => 'Email sent to guest requesting tour review',
                'template_key' => 'guest_review',
                'category' => 'guest'
            ],
            [
                'name' => 'Guest Booking Expired',
                'description' => 'Email sent to guest when booking expires',
                'template_key' => 'guest_expired_booking',
                'category' => 'guest'
            ]
        ];

        return view('admin.pages.setting.emails.maintenance', compact('emailTemplates'));
    }

    public function emailPreview($template, $locale)
    {
        // Set the application locale
        app()->setLocale($locale);
        
        // Generate mock data based on template type
        $data = $this->getMockEmailData($template);
        
        // Determine the correct view path
        $viewPath = $this->getEmailViewPath($template);
        
        return view($viewPath, $data);
    }

    public function emailPreviewAjax($template, $locale)
    {
        // Set the application locale
        app()->setLocale($locale);
        
        // Generate mock data based on template type
        $data = $this->getMockEmailData($template);
        
        // Determine the correct view path
        $viewPath = $this->getEmailViewPath($template);
        
        // Return just the HTML content for the modal
        return response()->json([
            'html' => view($viewPath, $data)->render(),
            'template_name' => $this->getTemplateName($template),
            'locale' => $locale
        ]);
    }

    private function getMockEmailData($template)
    {
        // Create mock user
        $mockUser = (object) [
            'id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+49 123 456 7890'
        ];
        
        // Create mock guide
        $mockGuide = (object) [
            'id' => 1,
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'email' => 'max.guide@example.com',
            'phone' => '+49 987 654 3210',
            'location' => 'Lake Constance, Germany',
            'user' => $mockUser
        ];
        
        // Create mock guiding
        $mockGuiding = (object) [
            'id' => 1,
            'title' => 'Professional Pike Fishing Adventure',
            'location' => 'Lake Constance, Germany',
            'slug' => 'professional-pike-fishing-adventure'
        ];
        
        // Create mock booking
        $mockBooking = (object) [
            'id' => 1,
            'token' => 'mock-booking-token-123',
            'book_date' => '2024-02-15',
            'count_of_users' => 2,
            'price' => 299.99,
            'extras' => serialize([
                [
                    'extra_name' => 'Fishing Equipment',
                    'extra_quantity' => 2,
                    'extra_total_price' => 50.00,
                    'price' => 50.00
                ],
                [
                    'extra_name' => 'Lunch Package',
                    'extra_quantity' => 2,
                    'extra_total_price' => 30.00,
                    'price' => 30.00
                ]
            ]),
            'additional_information' => 'Unfortunately, I have to cancel due to weather conditions. However, I can offer you these alternative dates.',
            'alternativeDates' => ['2024-02-20', '2024-02-22', '2024-02-25'],
            'textNote' => 'Thank you for your booking request. We have received it and are processing it.',
            'alternativeText' => 'Please let us know if any of the alternative dates work for you.',
            'guideName' => 'Max Mustermann',
            'user' => $mockUser
        ];
        
        // Base data that most templates need
        $baseData = [
            'user' => $mockUser,
            'guide' => $mockGuide,
            'guiding' => $mockGuiding,
            'booking' => $mockBooking
        ];
        
        // Add specific data based on template
        switch ($template) {
            case 'guest_tour_reminder':
                return array_merge($baseData, [
                    'guestName' => $mockUser->firstname,
                    'guideName' => $mockGuide->firstname,
                    'location' => $mockGuiding->location,
                    'date' => date('F j, Y', strtotime($mockBooking->book_date))
                ]);
                
            case 'guest_review':
                return array_merge($baseData, [
                    'userName' => $mockUser->firstname,
                    'guideName' => $mockGuide->firstname,
                    'location' => $mockGuiding->location,
                    'reviewUrl' => route('welcome') . '/review/mock-review-token'
                ]);
                
            case 'guest_booking_request':
                return array_merge($baseData, [
                    'textNote' => 'Thank you for your booking request. We have forwarded it to your selected guide.',
                    'alternativeText' => 'You will receive a confirmation email once the guide responds to your request.'
                ]);
                
            default:
                return $baseData;
        }
    }
    
    private function getEmailViewPath($template)
    {
        $templateMap = [
            'guide_booking_request' => 'mails.guide.guide_booking_request',
            'guide_reminder' => 'mails.guide.guide_reminder',
            'guide_reminder_12hrs' => 'mails.guide.guide_reminder_12hrs',
            'guide_accepted_mail' => 'mails.guide.guide_accepted_mail',
            'guide_expired_booking' => 'mails.guide.guide_expired_booking',
            'guide_upcoming_tour' => 'mails.guide.guide_upcoming_tour',
            'guest_booking_request' => 'mails.guest.guest_booking_request',
            'accepted_mail' => 'mails.guest.accepted_mail',
            'rejected_mail' => 'mails.guest.rejected_mail',
            'guest_tour_reminder' => 'mails.guest.guest_tour_reminder',
            'guest_review' => 'mails.guest.guest_review',
            'guest_expired_booking' => 'mails.guest.guest_expired_booking'
        ];
        
        return $templateMap[$template] ?? 'mails.guest.guest_booking_request';
    }

    private function getTemplateName($template)
    {
        $templateNames = [
            'guide_booking_request' => 'Guide Booking Request',
            'guide_reminder' => 'Guide Reminder 24hrs',
            'guide_reminder_12hrs' => 'Guide Reminder 12hrs',
            'guide_accepted_mail' => 'Guide Booking Accepted',
            'guide_expired_booking' => 'Guide Booking Expired',
            'guide_upcoming_tour' => 'Guide Upcoming Tour',
            'guest_booking_request' => 'Guest Booking Request',
            'accepted_mail' => 'Guest Booking Accepted',
            'rejected_mail' => 'Guest Booking Rejected',
            'guest_tour_reminder' => 'Guest Tour Reminder',
            'guest_review' => 'Guest Review Request',
            'guest_expired_booking' => 'Guest Booking Expired'
        ];
        
        return $templateNames[$template] ?? 'Unknown Template';
    }
}
