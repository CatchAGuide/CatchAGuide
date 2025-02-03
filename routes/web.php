<?php
use App\Http\Controllers\Admin\AuthenticationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\LoginAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\Blog\CategoriesController as AdminCategoriesController;
use App\Http\Controllers\Admin\Blog\ThreadsController as AdminThreadsController;
use App\Http\Controllers\Admin\BookingsController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\Admin\GuidesController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\GuidingsController as AdminGuidingsController;
use App\Http\Controllers\Admin\PageAttributeController;
use App\Http\Controllers\Admin\VacationsController as AdminVacationsController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Blog\CategoriesController;
use App\Http\Controllers\Blog\ThreadsController;
use App\Http\Controllers\Category\DestinationCountryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\GuidingsController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\SiteMapController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GuideThreadController;
use App\Http\Controllers\VacationsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PaymentsController as AdminPaymentsController;
use App\Http\Controllers\Admin\FAQController as AdminFaqController;
use App\Http\Controllers\Admin\Category\AdminCategoryCountryController;
use App\Http\Controllers\Admin\Category\AdminCategoryRegionController;
use App\Http\Controllers\Admin\Category\AdminCategoryCityController;
use App\Http\Controllers\Admin\NewBlog\GuideThreadsController as AdminGuideThreadsController;
use App\Http\Controllers\VacationBookingController;
use App\Http\Controllers\Admin\Category\AdminCategoryVacationCountryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('sitemap.xml',[SiteMapController::class, 'index']);
Route::post('/get-user-location', [WelcomeController::class,'getUserLocation'])->name('user.location');
Route::post('/language/switch', [App\Http\Controllers\LanguageController::class, 'switchLanguage'])->name('language.switch');

Route::get('/booking-accept/{token}',[BookingController::class,'accept'])->name('booking.accept');
Route::get('/booking-reject/{token}',[BookingController::class,'reject'])->name('booking.reject');
Route::post('/update/reject/{booking}',[BookingController::class,'rejectProcess'])->name('booking.rejection');

Route::get('/reject/success',function(){
    return view('pages.additional.reject_success');
})->name('booking.rejectsuccess');

Route::get('/booking/status}',function(){
    return view('pages.accepted');
})->name('status.accepted');

Route::get('/template-reject',function(){
    return view('pages.additional.rejected');
});

Route::get('/booking-request/thank-you',function(){
    return view('pages.additional.thank_you_request');
})->name('request.thank-you');

Route::get('thank-you/{booking}', [CheckoutController::class, 'thankYou'])->name('thank-you');

Route::get('/all-countries',function(){
    return view('pages.countries.index');
})->name('allcountries');

Route::post('/upload/{guiding?}', [FileUploadController::class, 'upload'])->name('upload');

Route::prefix('profile')->name('profile.')->middleware('auth:web')->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileController::class, 'index'])->name('index');
    Route::get('/settings', [App\Http\Controllers\ProfileController::class, 'settings'])->name('settings');
    Route::get('/z', [App\Http\Controllers\ProfileController::class, 'abbuchen'])->name('abbuchen');
    Route::get('/becomeguide', [App\Http\Controllers\ProfileController::class, 'becomeguide'])->name('becomeguide');
    Route::put('/account', [App\Http\Controllers\ProfileController::class, 'accountUpdate'])->name('account');
    Route::get('/favoriteguides', [App\Http\Controllers\ProfileController::class, 'favoriteguides'])->name('favoriteguides');
    Route::get('/myguidings', [App\Http\Controllers\ProfileController::class, 'myguidings'])->name('myguidings');

    Route::get('/myguidings/activate/{guiding}', [App\Http\Controllers\ProfileController::class, 'activate'])->name('guiding.activate');
    Route::get('/myguidings/deactivate/{guiding}', [App\Http\Controllers\ProfileController::class, 'deactivate'])->name('guiding.deactivate');

    Route::get('/bookings', [App\Http\Controllers\ProfileController::class, 'bookings'])->name('bookings');

    Route::get('showbooking/{bookingid}', [App\Http\Controllers\ProfileController::class, 'showbooking'])->name('showbooking');
    Route::get('stornobooking/{bookingid}', [App\Http\Controllers\ProfileController::class, 'stornobooking'])->name('stornobooking');

    Route::get('/guidebookings', [App\Http\Controllers\ProfileController::class, 'guidebookings'])->name('guidebookings');

    Route::get('/guidebookings/accept/{booking}', [App\Http\Controllers\ProfileController::class, 'accept'])->name('guidebookings.accept');
    Route::get('/guidebookings/reject/{booking}', [App\Http\Controllers\ProfileController::class, 'reject'])->name('guidebookings.reject');
    
    Route::get('/newguiding', [App\Http\Controllers\ProfileController::class, 'newguiding'])->name('newguiding');
    Route::post('/newguiding', [GuidingsController::class, 'guidingsStore'])->name('newguiding.store');
    Route::post('/newguiding/save-draft', [GuidingsController::class, 'saveDraft'])->name('newguiding.save-draft');

    Route::get('/payments', [App\Http\Controllers\ProfileController::class, 'payments'])->name('payments');
    Route::get('/calendar', [App\Http\Controllers\ProfileController::class, 'calendar'])->name('calendar');
    Route::post('/calendar/store', [\App\Http\Controllers\Api\EventsController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/delete/{id}', [\App\Http\Controllers\Api\EventsController::class, 'delete'])->name('calendar.delete');
    Route::post('/getbalance', [App\Http\Controllers\ProfileController::class, 'getbalance'])->name('getbalance');

    Route::get('process-merchant-status', [App\Http\Controllers\ProfileController::class, 'processMerchantStatus'])->name('processmerchantstatus');
});

Route::post('/guide', [GuidesController::class, 'store'])->name('guide');

Route::get('/info',function(){
    return phpinfo();
});

Route::middleware(['check_domain:catchaguide.com'])->group(function () {
    Route::prefix('fishing-magazine')->name('blog.')->group(function () {
        Route::resource('/categories', CategoriesController::class)->only(['show']);
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/{slug}', [ThreadsController::class, 'show'])->name('thread.show');
    });

});

Route::middleware(['check_domain:catchaguide.de'])->group(function () {
    Route::prefix('angelmagazin')->name('blogde.')->group(function () {
        Route::resource('/categories', CategoriesController::class)->only(['show']);
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/{slug}', [ThreadsController::class, 'show'])->name('thread.show');
    });
 
});

Route::post('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
Route::prefix('guides')->name('guides.')->group(function () {});

Route::get('/checkout', [CheckoutController::class, 'checkoutView'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');

Route::middleware('auth:web')->group(function () {
    Route::get('/transaction', [CheckoutController::class, 'completeTransaction'])->name('transaction');

    Route::get('events', [\App\Http\Controllers\Api\EventsController::class, 'index']);

    Route::get('chat',[ChatController::class, 'index'])->name('chat');
    Route::get('sendMessage/{user}', [ChatController::class, 'createChat'])->name('chat.create');

    Route::get('wishlist/add-or-remove/{guiding}', [\App\Http\Controllers\WishlistController::class, 'addOrRemove'])->name('wishlist.add-or-remove');

    Route::post('delete-image/{id}', [GuidingsController::class, 'deleteimage'])->name('delete-image');

    Route::get('deleteguiding/{id}', [GuidingsController::class, 'deleteguiding'])->name('deleteguiding');
    Route::get('delete-image/{guiding}/{img?}', [GuidingsController::class, 'deleteImage'])->name('deleteImage');

    Route::get('guidings/{guiding}/edit', [GuidingsController::class, 'edit'])->name('guidings.edit');
    Route::get('guidings/{guiding}/edit_newguiding', [GuidingsController::class, 'edit_newguiding'])->name('guidings.edit_newguiding');
    Route::post('guidings/{guiding}/update', [GuidingsController::class, 'update'])->name('guidings.update');
});

Route::get('guidings', [GuidingsController::class, 'index'])->name('guidings.index');
Route::get('guidings/{slug?}', [GuidingsController::class, 'redirectToNewFormat']);
Route::get('guidings/{id}/{slug}', [GuidingsController::class, 'newShow'])->name('guidings.show');
Route::post('newguidings', [GuidingsController::class, 'guidingsStore'])->name('guidings.store');

Route::resource('vacations', VacationsController::class);
Route::post('/vacation-booking', [VacationBookingController::class, 'store'])
    ->name('vacation.booking.store')
    ->middleware('web');
Route::get('vacations/location/{country}', [VacationsController::class, 'category'])->name('vacations.category');

Route::get('searchrequest', [GuidingsController::class, 'bookingrequest'])->name('guidings.request');
Route::post('searchrequest/store', [GuidingsController::class, 'bookingRequestStore'])->name('store.request');

Route::name('additional.')->group(function () {
    Route::view('/contact', 'pages.additional.contact')->name('contact');
    Route::view('/about-us', 'pages.additional.about-us')->name('about_us');
});

Route::get('destination', [DestinationCountryController::class, 'index'])->name('destination');
Route::get('destinationen', [DestinationCountryController::class, 'index'])->name('destination_de');
Route::get('destination/{country}/{region?}/{city?}', [DestinationCountryController::class, 'country'])->name('destination.country');

Route::post('sendcontact', [\App\Http\Controllers\ZoisController::class, 'sendcontact'])->name('sendcontactmail');
Route::post('sendnewsletter', [\App\Http\Controllers\ZoisController::class, 'sendnewsletter'])->name('sendnewsletter');

Route::name('ratings.')->prefix('ratings')->group(function () {
    Route::get('/{booking}', [RatingsController::class, 'show'])->name('show');
    Route::post('/store/{bookingid}', [RatingsController::class, 'store'])->name('store');
    Route::get('/review/{id}',[RatingsController::class, 'review'])->name('review');
});

Route::name('law.')->group(function() {
    Route::view('/imprint', 'pages.law.imprint')->name('imprint');
    Route::view('/data-protection', 'pages.law.data-protection')->name('data-protection');
    Route::view('/agb', 'pages.law.agb')->name('agb');
    Route::get('/faq', [FAQController::class, 'index'])->name('faq');
});

Route::get('login', [LoginAuthController::class, 'index'])->name('login');//->middleware('guest:employees');
Route::post('login', [LoginAuthController::class, 'login'])->name('login');//->middleware('guest:employees');
Route::post('register', [RegisterController::class, 'register'])->name('register');//->middleware('guest:employees');
Route::get('registration-verfication', [RegisterController::class, 'verfication'])->name('registration-verfication');//->middleware('guest:employees');
Route::get('password/reset', [ForgotPasswordController::class, 'index'])->name('password.request');
Route::post('password/reset', [ForgotPasswordController::class, 'reset'])->name('password.update');
Route::get('password/reset/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('test', [\App\Http\Controllers\TestController::class, 'index']);

Route::get('robots.txt', function () {
    if (request()->getHost() == 'catchaguide.com') {
        return response("User-agent: *\nDisallow: \nSitemap:https://catchaguide.com/NewENSitemap.xml", 200)
        ->header('Content-Type', 'text/plain');
    } else {
        return response("User-agent: *\nDisallow: \nSitemap:https://catchaguide.de/de/catchaguideDE.xml", 200)
        ->header('Content-Type', 'text/plain');
    }
});

Route::name('category.')->group(function(){
    Route::get('/{slug?}', [GuideThreadController::class, 'categoryIndex'])->name('thread');
});

Route::post('/change-password', [PasswordController::class, 'changePassword'])
    ->name('password.change')
    ->middleware('auth');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::name('auth.')->group(function () {
        Route::get('logins', [AuthenticationController::class, 'index'])->name('logins');//->middleware('guest:employees');
        Route::post('login', [AuthenticationController::class, 'login'])->name('login');//->middleware('guest:employees');
        Route::post('logout', [LoginAuthController::class, 'logout'])->name('logout');//->middleware('auth:employees');
    });

    Route::middleware('auth:employees')->group(function () {
        Route::view('/', 'admin.pages.index')->name('index');

        Route::resource('customers', CustomersController::class);
        Route::get('customersdelete/{id}', [CustomersController::class, 'customersdelete'])->name('customersdelete');
        Route::resource('guides', GuidesController::class);
        Route::get('guides/change-status/{guide}', [GuidesController::class, 'changeGuideStatus'])->name('guides.change-status');

        Route::prefix('page-attribute')->name('page-attribute.')->group(function () {
            Route::get('/en', [PageAttributeController::class,'index'])->name('en');
            Route::get('/de', [PageAttributeController::class,'indexDe'])->name('de');
            Route::post('/submit', [PageAttributeController::class,'store'])->name('store');
            Route::post('/update/{attribute}', [PageAttributeController::class,'update'])->name('update');
            Route::get('/destroy/{attribute}', [PageAttributeController::class,'destroy'])->name('destroy');
        });

        Route::prefix('faq')->name('faq.')->group(function () {
            Route::get('/home', [AdminFAQController::class,'home'])->name('home');
            Route::get('/search-request', [AdminFAQController::class,'searchRequest'])->name('searchrequest');
            Route::get('/create/{page}', [AdminFAQController::class,'create'])->name('create');
            Route::get('/edit/{faq}/{page}', [AdminFAQController::class,'edit'])->name('edit');
            Route::post('/store', [AdminFAQController::class,'store'])->name('store');
            Route::post('/update/{faq}', [AdminFAQController::class,'update'])->name('update');
            Route::get('/destroy/{faq}', [AdminFAQController::class,'destroy'])->name('destroy');
        });

        Route::resource('guidings', AdminGuidingsController::class);
        Route::get('guidings/changeguidingstatus/{id}', [AdminGuidingsController::class, 'changeguidingstatus'])->name('changeGuidingStatus');
        Route::resource('bookings', BookingsController::class);
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [AdminPaymentsController::class, 'index'])->name('index');
            Route::get('/showoutpayments/{id}', [AdminPaymentsController::class, 'showoutpayments'])->name('showoutpayments');
            Route::get('/aproveoutpayments/{id}', [AdminPaymentsController::class, 'aproveoutpayments'])->name('aproveoutpayments');
            Route::get('/deletepayments/{id}', [AdminPaymentsController::class, 'deletepayments'])->name('deletepayments');
        });

        Route::resource('vacations', AdminVacationsController::class)->except('show');
        Route::get('vacations/changeVacationStatus/{id}', [AdminVacationsController::class, 'changeVacationStatus'])->name('changeVacationStatus');
        Route::get('vacations/bookings', [AdminVacationsController::class, 'bookings'])->name('vacations.bookings');
        Route::get('vacations/bookings/{booking}', [AdminVacationsController::class, 'show'])->name('vacations.bookings.show');
        Route::get('vacations/{id}/{slug}', [VacationsController::class, 'show'])->name('vacations.show');

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/targets', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'targetIndex'])->name('targetindex');
            Route::post('/storetarget', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'storetarget'])->name('storetarget');
            Route::put('/updatetarget/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'updatetarget'])->name('updatetarget');
            Route::get('/deletetarget/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'deletetarget'])->name('deletetarget');

            Route::get('/methods', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'methodIndex'])->name('methodindex');
            Route::post('/storemethod', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'storemethod'])->name('storemethod');
            Route::put('/updatemethod/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'updatemethod'])->name('updatemethod');
            Route::get('/deletemethod/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'deletemethod'])->name('deletemethod');

            Route::get('/waters', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'waterIndex'])->name('waterindex');
            Route::post('/storewater', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'storewater'])->name('storewater');
            Route::put('/updatewater/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'updatewater'])->name('updatewater');
            Route::get('/deletewater/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'deletewater'])->name('deletewater');

            Route::get('/inclussions', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'inclussionIndex'])->name('inclussionindex');
            Route::post('/storeinclussion', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'storeinclussion'])->name('storeinclussion');
            Route::put('/updateinclussion/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'updateinclussion'])->name('updateinclussion');
            Route::get('/deleteinclussion/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'deleteinclussion'])->name('deleteinclussion');

            Route::get('/fishingfrom', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'fishingfromIndex'])->name('fishingfromindex');
            Route::post('/storefishingfrom', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'storefishingfrom'])->name('storefishingfrom');
            Route::put('/updatefishingfrom/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'updatefishingfrom'])->name('updatefishingfrom');
            Route::get('/deletefishingfrom/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'deletefishingfrom'])->name('deletefishingfrom');

            Route::get('/fishingtype', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'fishingtypeIndex'])->name('fishingtypeindex');
            Route::post('/storefishingtype', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'storefishingtype'])->name('storefishingtype');
            Route::put('/updatefishingtype/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'updatefishingtype'])->name('updatefishingtype');
            Route::get('/deletefishingtype/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'deletefishingtype'])->name('deletefishingtype');

            Route::get('/equipment', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'equipmentIndex'])->name('equipmentindex');
            Route::post('/storeequipment', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'storeequipment'])->name('storeequipment');
            Route::put('/updateequipment/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'updatefishingequipment'])->name('updatefishingequipment');
            Route::get('/deleteequipment/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'deleteequipment'])->name('deleteequipment');

            Route::get('/levels', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'levelIndex'])->name('levelindex');
            Route::post('/storelevel', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'storelevel'])->name('storelevel');
            Route::put('/updatelevel/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'updatelevel'])->name('updatelevel');
            Route::get('/deletelevel/{id}', [\App\Http\Controllers\Admin\GuidingsSettingController::class, 'deletelevel'])->name('deletelevel');
        });

        Route::get('/translation/create', [TranslationController::class,'create'])->name('translation.create');
        
        Route::resource('employees', EmployeesController::class);

        Route::prefix('blog')->name('blog.')->group(function () {
            Route::resource('threads', AdminThreadsController::class);
            Route::get('threads/{thread}/delete', [AdminThreadsController::class, 'delete'])->name('delete');
            Route::resource('categories', AdminCategoriesController::class);
            Route::get('categories/{category}/delete', [AdminCategoriesController::class, 'delete'])->name('category.delete');
        });

        Route::prefix('newblog')->name('newblog.')->group(function () {
            Route::resource('threads', AdminGuideThreadsController::class);
            Route::get('threads/{thread}/delete', [AdminGuideThreadsController::class, 'delete'])->name('delete');
        });

        Route::prefix('category')->name('category.')->group(function () {
            Route::resource('country', AdminCategoryCountryController::class);
            Route::resource('vacation-country', AdminCategoryVacationCountryController::class);
            Route::resource('region', AdminCategoryRegionController::class);
            Route::resource('city', AdminCategoryCityController::class);
        });

        Route::get('request-as-guide', [\App\Http\Controllers\GuideRequestsController::class, 'index'])->name('guide-requests.index');
    });
});