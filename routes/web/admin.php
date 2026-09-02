<?php

use App\Http\Controllers\Admin\AccommodationsController as AdminAccommodationsController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthenticationController;
use App\Http\Controllers\Admin\BookingsController;
use App\Http\Controllers\Admin\CampVacationBookingsController;
use App\Http\Controllers\Admin\CampsController;
use App\Http\Controllers\Admin\Category\AdminCategoryCityController;
use App\Http\Controllers\Admin\Category\AdminCategoryCountryController;
use App\Http\Controllers\Admin\Category\AdminCategoryDestinationHubController;
use App\Http\Controllers\Admin\Category\AdminCategoryHubController;
use App\Http\Controllers\Admin\Category\AdminCategoryMethodsController;
use App\Http\Controllers\Admin\Category\AdminCategoryRegionController;
use App\Http\Controllers\Admin\Category\AdminCategoryTargetFishController;
use App\Http\Controllers\Admin\ConsolidatedListingsController;
use App\Http\Controllers\Admin\ContactRequestsController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\EmailLogsController;
use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\Admin\FAQController as AdminFaqController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\FinancialDashboardController;
use App\Http\Controllers\Admin\GuideAnalyticsController;
use App\Http\Controllers\Admin\GuideRequestReviewController;
use App\Http\Controllers\Admin\GuidesController;
use App\Http\Controllers\Admin\GuidingsController as AdminGuidingsController;
use App\Http\Controllers\Admin\GuidingsSettingController;
use App\Http\Controllers\Admin\MonthlyHighlightController;
use App\Http\Controllers\Admin\NewsletterSubscribersController;
use App\Http\Controllers\Admin\OfferSendoutController;
use App\Http\Controllers\Admin\PageAttributeController;
use App\Http\Controllers\Admin\PaymentsController as AdminPaymentsController;
use App\Http\Controllers\Admin\ProductReportsController;
use App\Http\Controllers\Admin\ReviewsController;
use App\Http\Controllers\Admin\ScheduledTasksController;
use App\Http\Controllers\Admin\SpecialOffersController;
use App\Http\Controllers\Admin\StrategyController;
use App\Http\Controllers\Admin\AdminTermsSectionController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\TripBookingsController;
use App\Http\Controllers\Admin\TripsController as AdminTripsController;
use App\Http\Controllers\Admin\VacationsController as AdminVacationsController;
use App\Http\Controllers\Admin\VacationTestimonialsController;
use App\Http\Controllers\Admin\Blog\CategoriesController as AdminCategoriesController;
use App\Http\Controllers\Admin\Blog\ThreadsController as AdminThreadsController;
use App\Http\Controllers\Admin\NewBlog\GuideThreadsController as AdminGuideThreadsController;
use App\Http\Controllers\Admin\RentalBoatsController as AdminRentalBoatsController;
use App\Http\Controllers\Auth\LoginAuthController;
use App\Http\Controllers\GuideRequestsController;
use App\Http\Controllers\VacationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::name('auth.')->group(function () {
        Route::get('logins', [AuthenticationController::class, 'index'])->name('logins');
        Route::post('login', [AuthenticationController::class, 'login'])
            ->middleware('throttle:login')
            ->name('login');
        Route::post('logout', [LoginAuthController::class, 'logout'])->name('logout');
    });

    Route::middleware('auth:employees')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');

        Route::resource('customers', CustomersController::class);
        Route::get('customersdelete/{id}', [CustomersController::class, 'customersdelete'])->name('customersdelete');
        Route::resource('guides', GuidesController::class);
        Route::get('guides/change-status/{guide}', [GuidesController::class, 'changeGuideStatus'])->name('guides.change-status');
        Route::get('guide-analytics', [GuideAnalyticsController::class, 'index'])->name('guide-analytics.index');

        Route::prefix('page-attribute')->name('page-attribute.')->group(function () {
            Route::get('/en', [PageAttributeController::class, 'index'])->name('en');
            Route::get('/de', [PageAttributeController::class, 'indexDe'])->name('de');
            Route::post('/submit', [PageAttributeController::class, 'store'])->name('store');
            Route::post('/update/{attribute}', [PageAttributeController::class, 'update'])->name('update');
            Route::get('/destroy/{attribute}', [PageAttributeController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('faq')->name('faq.')->group(function () {
            Route::get('/home', [AdminFaqController::class, 'home'])->name('home');
            Route::get('/search-request', [AdminFaqController::class, 'searchRequest'])->name('searchrequest');
            Route::get('/vacations', [AdminFaqController::class, 'vacations'])->name('vacations');
            Route::get('/vacation-trips', [AdminFaqController::class, 'vacationTrips'])->name('vacation-trips');
            Route::get('/vacation-camps', [AdminFaqController::class, 'vacationCamps'])->name('vacation-camps');
            Route::get('/create/{page}', [AdminFaqController::class, 'create'])->name('create');
            Route::get('/edit/{faq}/{page}', [AdminFaqController::class, 'edit'])->name('edit');
            Route::post('/store', [AdminFaqController::class, 'store'])->name('store');
            Route::post('/update/{faq}', [AdminFaqController::class, 'update'])->name('update');
            Route::get('/destroy/{faq}', [AdminFaqController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('terms')->name('terms.')->group(function () {
            Route::get('/', [AdminTermsSectionController::class, 'index'])->name('index');
            Route::get('/create', [AdminTermsSectionController::class, 'create'])->name('create');
            Route::post('/', [AdminTermsSectionController::class, 'store'])->name('store');
            Route::post('/reorder', [AdminTermsSectionController::class, 'reorder'])->name('reorder');
            Route::get('/{termsSection}/edit', [AdminTermsSectionController::class, 'edit'])->name('edit');
            Route::put('/{termsSection}', [AdminTermsSectionController::class, 'update'])->name('update');
            Route::delete('/{termsSection}', [AdminTermsSectionController::class, 'destroy'])->name('destroy');
            Route::get('/{termsSection}/translation', [AdminTermsSectionController::class, 'getTranslation'])->name('translation');
        });

        Route::resource('monthly-highlights', MonthlyHighlightController::class)->except(['show']);
        Route::resource('vacation-testimonials', VacationTestimonialsController::class)->except(['show']);

        Route::get('guidings/search', [AdminGuidingsController::class, 'searchForSelect'])->name('guidings.search');
        Route::get('guidings/{guiding}/details', [AdminGuidingsController::class, 'details'])->name('guidings.details');
        Route::post('guidings/{guiding}/details-field', [AdminGuidingsController::class, 'updateDetailsField'])->name('guidings.details-field');
        Route::post('guidings/{guiding}/translate', [AdminGuidingsController::class, 'translate'])
            ->middleware('throttle:gemini-translation')
            ->name('guidings.translate');
        Route::post('guidings/{guiding}/language', [AdminGuidingsController::class, 'updateLanguage'])->name('guidings.update-language');
        Route::post('guidings/{guiding}/restore-images', [AdminGuidingsController::class, 'restoreImages'])->name('guidings.restore-images');
        Route::resource('guidings', AdminGuidingsController::class);
        Route::get('guidings/changeguidingstatus/{id}', [AdminGuidingsController::class, 'changeguidingstatus'])->name('changeGuidingStatus');

        Route::resource('rental-boats', AdminRentalBoatsController::class);
        Route::get('rental-boats/change-status/{id}', [AdminRentalBoatsController::class, 'changeStatus'])->name('rental-boats.change-status');

        Route::resource('accommodations', AdminAccommodationsController::class);
        Route::get('accommodations/change-status/{id}', [AdminAccommodationsController::class, 'changeStatus'])->name('accommodations.change-status');

        Route::prefix('listings')->name('listings.')->group(function () {
            Route::get('consolidated', [ConsolidatedListingsController::class, 'index'])->name('consolidated.index');
            Route::get('consolidated/export', [ConsolidatedListingsController::class, 'export'])->name('consolidated.export');
        });

        Route::resource('camps', CampsController::class);
        Route::get('camps/change-status/{id}', [CampsController::class, 'changeStatus'])->name('camps.change-status');

        Route::resource('trips', AdminTripsController::class);
        Route::get('trips/change-status/{id}', [AdminTripsController::class, 'changeStatus'])->name('trips.change-status');

        Route::resource('special-offers', SpecialOffersController::class);
        Route::get('special-offers/change-status/{id}', [SpecialOffersController::class, 'changeStatus'])->name('special-offers.change-status');

        Route::get('/bookings/guidings-search', [BookingsController::class, 'searchGuidings'])->name('bookings.guidings-search');
        Route::get('/bookings/{booking}/reschedule-data', [BookingsController::class, 'rescheduleData'])->name('bookings.reschedule-data');
        Route::post('/bookings/{booking}/reschedule', [BookingsController::class, 'rescheduleInPlace'])->name('bookings.reschedule');
        Route::resource('bookings', BookingsController::class);
        Route::post('/bookings/{booking}/save', [BookingsController::class, 'update'])->name('bookings.save');
        Route::get('/bookings/{booking}/email-preview', [BookingsController::class, 'emailPreview'])->name('bookings.email-preview');
        Route::get('/bookings/{booking}/guide-invoice-preview', [BookingsController::class, 'guideInvoicePreview'])->name('bookings.guide-invoice-preview');
        Route::post('/bookings/{booking}/send-booking-request-emails', [BookingsController::class, 'sendBookingRequestEmails'])->name('bookings.send-booking-request-emails');
        Route::post('/bookings/{booking}/send-guide-invoice', [BookingsController::class, 'sendGuideInvoice'])->name('bookings.send-guide-invoice');
        Route::post('/bookings/{booking}/update-guide-billing-status', [BookingsController::class, 'updateGuideBillingStatus'])->name('bookings.update-guide-billing-status');

        Route::get('trip-bookings/trips-search', [TripBookingsController::class, 'searchTrips'])->name('trip-bookings.trips-search');
        Route::post('trip-bookings', [TripBookingsController::class, 'storeManual'])->name('trip-bookings.store');

        Route::get('camp-vacation-bookings/sources-search', [CampVacationBookingsController::class, 'searchSources'])->name('camp-vacation-bookings.sources-search');
        Route::post('camp-vacation-bookings', [CampVacationBookingsController::class, 'storeManual'])->name('camp-vacation-bookings.store');

        Route::get('camp-vacation-bookings', [CampVacationBookingsController::class, 'index'])->name('camp-vacation-bookings.index');
        Route::patch('camp-vacation-bookings/{campVacationBooking}/status', [CampVacationBookingsController::class, 'updateStatus'])->name('camp-vacation-bookings.update-status');
        Route::post('camp-vacation-bookings/reply', [CampVacationBookingsController::class, 'sendReply'])->name('camp-vacation-bookings.reply');
        Route::get('camp-vacation-bookings/{campVacationBooking}/email-history', [CampVacationBookingsController::class, 'emailHistory'])->name('camp-vacation-bookings.email-history');

        Route::get('trip-bookings', [TripBookingsController::class, 'index'])->name('trip-bookings.index');
        Route::get('trip-bookings/{tripBooking}/comment', [TripBookingsController::class, 'showComment'])->name('trip-bookings.comment.show');
        Route::post('trip-bookings/{tripBooking}/comment', [TripBookingsController::class, 'updateComment'])->name('trip-bookings.comment.update');
        Route::match(['patch', 'post'], 'trip-bookings/{tripBooking}/status', [TripBookingsController::class, 'updateStatus'])->name('trip-bookings.update-status');
        Route::post('trip-bookings/reply', [TripBookingsController::class, 'sendReply'])->name('trip-bookings.reply');
        Route::get('trip-bookings/{tripBooking}/email-history', [TripBookingsController::class, 'emailHistory'])->name('trip-bookings.email-history');

        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('analytics', [FinanceController::class, 'analytics'])->name('analytics');
            Route::get('invoices', [FinanceController::class, 'invoices'])->name('invoices');
            Route::get('invoices/export', [FinanceController::class, 'exportInvoices'])->name('invoices.export');
            Route::patch('{source}/{id}/invoice', [FinanceController::class, 'updateInvoice'])
                ->where('source', '^(booking|trip|camp_vacation)$')
                ->name('update-invoice');
            Route::patch('{source}/{id}/paid', [FinanceController::class, 'updatePaid'])
                ->where('source', '^(booking|trip|camp_vacation)$')
                ->name('update-paid');
        });

        Route::get('financial/dashboard', [FinancialDashboardController::class, 'index'])->name('financial.dashboard');
        Route::get('financial/dashboard/export', [FinancialDashboardController::class, 'export'])->name('financial.dashboard.export');

        Route::prefix('strategy')->name('strategy.')->group(function () {
            Route::get('/', [StrategyController::class, 'index'])->name('index');
            Route::get('/supply-gaps', [StrategyController::class, 'supplyGaps'])->name('supply-gaps');
            Route::get('/content-coverage', [StrategyController::class, 'contentCoverage'])->name('content-coverage');
        });

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
            Route::get('/targets', [GuidingsSettingController::class, 'targetIndex'])->name('targetindex');
            Route::post('/storetarget', [GuidingsSettingController::class, 'storetarget'])->name('storetarget');
            Route::put('/updatetarget/{id}', [GuidingsSettingController::class, 'updatetarget'])->name('updatetarget');
            Route::get('/deletetarget/{id}', [GuidingsSettingController::class, 'deletetarget'])->name('deletetarget');

            Route::get('/methods', [GuidingsSettingController::class, 'methodIndex'])->name('methodindex');
            Route::post('/storemethod', [GuidingsSettingController::class, 'storemethod'])->name('storemethod');
            Route::put('/updatemethod/{id}', [GuidingsSettingController::class, 'updatemethod'])->name('updatemethod');
            Route::get('/deletemethod/{id}', [GuidingsSettingController::class, 'deletemethod'])->name('deletemethod');

            Route::get('/waters', [GuidingsSettingController::class, 'waterIndex'])->name('waterindex');
            Route::post('/storewater', [GuidingsSettingController::class, 'storewater'])->name('storewater');
            Route::put('/updatewater/{id}', [GuidingsSettingController::class, 'updatewater'])->name('updatewater');
            Route::get('/deletewater/{id}', [GuidingsSettingController::class, 'deletewater'])->name('deletewater');

            Route::get('/inclussions', [GuidingsSettingController::class, 'inclussionIndex'])->name('inclussionindex');
            Route::post('/storeinclussion', [GuidingsSettingController::class, 'storeinclussion'])->name('storeinclussion');
            Route::put('/updateinclussion/{id}', [GuidingsSettingController::class, 'updateinclussion'])->name('updateinclussion');
            Route::get('/deleteinclussion/{id}', [GuidingsSettingController::class, 'deleteinclussion'])->name('deleteinclussion');

            Route::get('/fishingfrom', [GuidingsSettingController::class, 'fishingfromIndex'])->name('fishingfromindex');
            Route::post('/storefishingfrom', [GuidingsSettingController::class, 'storefishingfrom'])->name('storefishingfrom');
            Route::put('/updatefishingfrom/{id}', [GuidingsSettingController::class, 'updatefishingfrom'])->name('updatefishingfrom');
            Route::get('/deletefishingfrom/{id}', [GuidingsSettingController::class, 'deletefishingfrom'])->name('deletefishingfrom');

            Route::get('/fishingtype', [GuidingsSettingController::class, 'fishingtypeIndex'])->name('fishingtypeindex');
            Route::post('/storefishingtype', [GuidingsSettingController::class, 'storefishingtype'])->name('storefishingtype');
            Route::put('/updatefishingtype/{id}', [GuidingsSettingController::class, 'updatefishingtype'])->name('updatefishingtype');
            Route::get('/deletefishingtype/{id}', [GuidingsSettingController::class, 'deletefishingtype'])->name('deletefishingtype');

            Route::get('/equipment', [GuidingsSettingController::class, 'equipmentIndex'])->name('equipmentindex');
            Route::post('/storeequipment', [GuidingsSettingController::class, 'storeequipment'])->name('storeequipment');
            Route::put('/updateequipment/{id}', [GuidingsSettingController::class, 'updatefishingequipment'])->name('updatefishingequipment');
            Route::get('/deleteequipment/{id}', [GuidingsSettingController::class, 'deleteequipment'])->name('deleteequipment');

            Route::get('/levels', [GuidingsSettingController::class, 'levelIndex'])->name('levelindex');
            Route::post('/storelevel', [GuidingsSettingController::class, 'storelevel'])->name('storelevel');
            Route::put('/updatelevel/{id}', [GuidingsSettingController::class, 'updatelevel'])->name('updatelevel');
            Route::get('/deletelevel/{id}', [GuidingsSettingController::class, 'deletelevel'])->name('deletelevel');

            Route::get('/boat-extras', [GuidingsSettingController::class, 'boatExtrasIndex'])->name('boat-extras.index');
            Route::post('/boat-extras', [GuidingsSettingController::class, 'storeBoatExtra'])->name('boat-extras.store');
            Route::put('/boat-extras/{id}', [GuidingsSettingController::class, 'updateBoatExtra'])->name('boat-extras.update');
            Route::delete('/boat-extras/{id}', [GuidingsSettingController::class, 'deleteBoatExtra'])->name('boat-extras.destroy');

            Route::get('/facilities', [GuidingsSettingController::class, 'facilitiesIndex'])->name('facilities.index');
            Route::post('/facilities', [GuidingsSettingController::class, 'storeFacility'])->name('facilities.store');
            Route::put('/facilities/{id}', [GuidingsSettingController::class, 'updateFacility'])->name('facilities.update');
            Route::delete('/facilities/{id}', [GuidingsSettingController::class, 'deleteFacility'])->name('facilities.destroy');

            Route::get('/kitchen-equipment', [GuidingsSettingController::class, 'kitchenEquipmentIndex'])->name('kitchen-equipment.index');
            Route::post('/kitchen-equipment', [GuidingsSettingController::class, 'storeKitchenEquipment'])->name('kitchen-equipment.store');
            Route::put('/kitchen-equipment/{id}', [GuidingsSettingController::class, 'updateKitchenEquipment'])->name('kitchen-equipment.update');
            Route::delete('/kitchen-equipment/{id}', [GuidingsSettingController::class, 'deleteKitchenEquipment'])->name('kitchen-equipment.destroy');

            Route::post('/scheduled-tasks/custom', [ScheduledTasksController::class, 'storeCustom'])->name('scheduled-tasks.custom.store');
            Route::put('/scheduled-tasks/custom/{customScheduledTask}', [ScheduledTasksController::class, 'updateCustom'])->name('scheduled-tasks.custom.update');
            Route::delete('/scheduled-tasks/custom/{customScheduledTask}', [ScheduledTasksController::class, 'destroyCustom'])->name('scheduled-tasks.custom.destroy');

            Route::get('/scheduled-tasks', [ScheduledTasksController::class, 'index'])->name('scheduled-tasks.index');
            Route::put('/scheduled-tasks/{key}', [ScheduledTasksController::class, 'update'])->name('scheduled-tasks.update');

            Route::get('/emailmaintenance', [GuidingsSettingController::class, 'emailmaintenance'])->name('emailmaintenance');
            Route::get('/email-preview/{template}/{locale}', [GuidingsSettingController::class, 'emailPreview'])->name('email.preview');
            Route::get('/email-preview-ajax/{template}/{locale}', [GuidingsSettingController::class, 'emailPreviewAjax'])->name('email.preview.ajax');
        });

        Route::get('/translation/create', [TranslationController::class, 'create'])->name('translation.create');

        Route::get('employees/trashed', [EmployeesController::class, 'trashed'])->name('employees.trashed');
        Route::post('employees/trashed/{id}/restore', [EmployeesController::class, 'restore'])->name('employees.restore');
        Route::post('employees/{employee}/reset-password', [EmployeesController::class, 'resetPassword'])->name('employees.reset-password');
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
            Route::get('/', [AdminCategoryHubController::class, 'index'])->name('hub');
            Route::get('destination-hub', [AdminCategoryDestinationHubController::class, 'edit'])->name('destination-hub.edit');
            Route::put('destination-hub', [AdminCategoryDestinationHubController::class, 'update'])->name('destination-hub.update');
            Route::get('destination-hub/language-data', [AdminCategoryDestinationHubController::class, 'getLanguageData'])->name('destination-hub.language-data');
            Route::post('destination-hub/autosave', [AdminCategoryDestinationHubController::class, 'autosave'])->name('destination-hub.autosave');

            Route::get('country/{id}/translation', [AdminCategoryCountryController::class, 'getTranslation'])->name('country.translation');
            Route::get('country/{id}/language-data', [AdminCategoryCountryController::class, 'getLanguageData'])->name('country.language-data');
            Route::post('country/{id}/autosave', [AdminCategoryCountryController::class, 'autosave'])->name('country.autosave');
            Route::get('region/{id}/translation', [AdminCategoryRegionController::class, 'getTranslation'])->name('region.translation');
            Route::get('region/{id}/language-data', [AdminCategoryRegionController::class, 'getLanguageData'])->name('region.language-data');
            Route::post('region/{id}/autosave', [AdminCategoryRegionController::class, 'autosave'])->name('region.autosave');
            Route::get('city/{id}/translation', [AdminCategoryCityController::class, 'getTranslation'])->name('city.translation');
            Route::get('city/{id}/language-data', [AdminCategoryCityController::class, 'getLanguageData'])->name('city.language-data');
            Route::post('city/{id}/autosave', [AdminCategoryCityController::class, 'autosave'])->name('city.autosave');
            Route::resource('country', AdminCategoryCountryController::class);
            Route::resource('region', AdminCategoryRegionController::class);
            Route::resource('city', AdminCategoryCityController::class);
            Route::resource('methods', AdminCategoryMethodsController::class);
            Route::post('methods/toggle-favorite', [AdminCategoryMethodsController::class, 'toggleFavorite'])->name('methods.toggle-favorite');
            Route::get('methods/{id}/language-data', [AdminCategoryMethodsController::class, 'getLanguageData'])->name('methods.language-data');
            Route::post('methods/{id}/autosave', [AdminCategoryMethodsController::class, 'autosave'])->name('methods.autosave');
            Route::resource('target-fish', AdminCategoryTargetFishController::class);
            Route::post('target-fish/toggle-favorite', [AdminCategoryTargetFishController::class, 'toggleFavorite'])->name('target-fish.toggle-favorite');
            Route::get('target-fish/{id}/language-data', [AdminCategoryTargetFishController::class, 'getLanguageData'])->name('target-fish.language-data');
            Route::post('target-fish/{id}/autosave', [AdminCategoryTargetFishController::class, 'autosave'])->name('target-fish.autosave');
        });

        Route::get('request-as-guide', [GuideRequestsController::class, 'index'])->name('guide-requests.index');
        Route::post('guide-requests/{guideRequest}/approve', [GuideRequestReviewController::class, 'approve'])->name('guide-requests.approve');
        Route::post('guide-requests/{guideRequest}/reject', [GuideRequestReviewController::class, 'reject'])->name('guide-requests.reject');

        Route::get('email-logs', [EmailLogsController::class, 'index'])->name('email-logs.index');
        Route::get('email-logs/{emailLog}', [EmailLogsController::class, 'show'])->name('email-logs.show');
        Route::get('reviews', [ReviewsController::class, 'index'])->name('reviews.index');
        Route::get('reviews/{review}', [ReviewsController::class, 'show'])->name('reviews.show');
        Route::get('contact-requests', [ContactRequestsController::class, 'index'])->name('contact-requests.index');
        Route::get('contact-requests/{contactSubmission}/comment', [ContactRequestsController::class, 'showComment'])->name('contact-requests.comment.show');
        Route::post('contact-requests/{contactSubmission}/comment', [ContactRequestsController::class, 'updateComment'])->name('contact-requests.comment.update');
        Route::post('contact-requests/reply', [ContactRequestsController::class, 'sendReply'])->name('contact-requests.reply');
        Route::patch('contact-requests/{contactSubmission}/status', [ContactRequestsController::class, 'updateStatus'])->name('contact-requests.update-status');

        Route::get('product-reports', [ProductReportsController::class, 'index'])->name('product-reports.index');
        Route::get('product-reports/{productReport}/comment', [ProductReportsController::class, 'showComment'])->name('product-reports.comment.show');
        Route::post('product-reports/{productReport}/comment', [ProductReportsController::class, 'updateComment'])->name('product-reports.comment.update');
        Route::patch('product-reports/{productReport}/status', [ProductReportsController::class, 'updateStatus'])->name('product-reports.update-status');

        Route::get('newsletter-subscribers', [NewsletterSubscribersController::class, 'index'])->name('newsletter-subscribers.index');
        Route::delete('newsletter-subscribers/{newsletter}', [NewsletterSubscribersController::class, 'destroy'])->name('newsletter-subscribers.destroy');

        Route::prefix('offer-sendout')->name('offer-sendout.')->group(function () {
            Route::get('/', [OfferSendoutController::class, 'customCampOffers'])->name('index');
            Route::get('/create', [OfferSendoutController::class, 'create'])->name('create');
            Route::get('/custom-camp-offers/{customCampOffer}', [OfferSendoutController::class, 'getCustomCampOffer'])->name('custom-camp-offers.show');
            Route::patch('/custom-camp-offers/{customCampOffer}/status', [OfferSendoutController::class, 'updateStatus'])->name('custom-camp-offers.update-status');
            Route::post('/custom-camp-offers/{customCampOffer}/follow-up', [OfferSendoutController::class, 'sendFollowUp'])->name('custom-camp-offers.follow-up');
            Route::get('/camp-options/{camp}', [OfferSendoutController::class, 'campOptions'])->name('camp-options');
            Route::post('/preview', [OfferSendoutController::class, 'preview'])->name('preview');
            Route::post('/send', [OfferSendoutController::class, 'send'])->name('send');
        });
    });
});

Route::middleware(['web', 'auth:employees'])->group(function () {
    Route::get('/api/admin/financial-dashboard', [FinancialDashboardController::class, 'data'])
        ->name('admin.api.financial-dashboard');
});
