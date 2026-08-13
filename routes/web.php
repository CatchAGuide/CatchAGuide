<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Domain files live in routes/web/. Load order is part of routing:
| profile (guiding edit) must register before catalog destination
| catch-alls, and category.thread must stay last.
|
*/

require __DIR__.'/web/core.php';
require __DIR__.'/web/bookings.php';
require __DIR__.'/web/profile.php';
require __DIR__.'/web/checkout.php';
require __DIR__.'/web/catalog.php';
require __DIR__.'/web/content.php';
require __DIR__.'/web/auth.php';
require __DIR__.'/web/admin.php';
require __DIR__.'/web/catch-all.php';
