<!-- app-Header -->
<div class="app-header header sticky">
    <div class="container-fluid main-container">
        <div class="d-flex">
            <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="#"></a>
            <!-- sidebar-toggle-->
            <a class="logo-horizontal admin-header-logo" href="{{ route('welcome') }}">
                <img src="{{ asset('assets/images/logo/CatchAGuide_Logo_PNG.png') }}" class="header-brand-img desktop-logo" alt="{{ config('app.name') }}">
                <img src="{{ asset('assets/images/logo/CatchAGuide_Logo_PNG.png') }}" class="header-brand-img light-logo1" alt="{{ config('app.name') }}">
            </a>

            <nav class="admin-breadcrumb ms-3 d-none d-md-flex align-items-center" aria-label="Breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('admin.administration') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $adminBreadcrumbTitle ?? 'Dashboard' }}</li>
                </ol>
            </nav>

            <div class="d-flex order-lg-2 ms-auto header-right-icons align-items-center">
                <!-- Bell always visible (including mobile) – not inside burger dropdown -->
                <div class="dropdown notification-dropdown admin-header-bell me-2">
                    <a href="#" class="nav-link icon" data-bs-toggle="dropdown" aria-label="Notifications">
                        <i class="fe fe-bell"></i>
                        @if(($adminNotificationCount ?? 0) > 0)
                            <span class="notification-badge">
                                {{ $adminNotificationCount > 9 ? '9+' : $adminNotificationCount }}
                            </span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow p-0">
                        <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="notification-pill"></span>
                                <span class="fw-semibold small">{{ __('Notifications') }}</span>
                            </div>
                            <span class="badge bg-secondary-subtle text-white-50 rounded-pill small">
                                {{ $adminNotificationCount ?? 0 }}
                            </span>
                        </div>
                        <div class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;">
                            @forelse(($adminNotifications ?? []) as $notification)
                                <a href="{{ $notification->link ?? '#' }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                    <div class="me-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-soft badge-soft--{{ $notification->level ?? 'info' }} text-uppercase">{{ $notification->type }}</span>
                                        </div>
                                        <div class="fw-semibold small mt-1">{{ $notification->title }}</div>
                                        @if($notification->body)
                                            <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($notification->body, 80) }}</small>
                                        @endif
                                    </div>
                                    <small class="text-muted text-nowrap ms-2">
                                        {{ $notification->created_at?->diffForHumans() }}
                                    </small>
                                </a>
                            @empty
                                <div class="list-group-item text-muted small">
                                    {{ __('No new notifications') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="dropdown d-lg-none d-md-block d-none">
                    <a href="#" class="nav-link icon" data-bs-toggle="dropdown">
                        <i class="fe fe-search"></i>
                    </a>
                    <div class="dropdown-menu header-search dropdown-menu-start">
                        <div class="input-group w-100 p-2">
                            <input type="text" class="form-control" placeholder="Search....">
                            <div class="input-group-text btn btn-primary">
                                <i class="fe fe-search" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="navbar-toggler navresponsive-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4" aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon fe fe-more-vertical"></span>
                </button>
                <div class="navbar navbar-collapse responsive-navbar p-0">
                    <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                        <div class="d-flex order-lg-2 align-items-center gap-2">
                            <form action="{{ route('language.switch') }}" method="POST" class="d-flex align-items-center admin-language-form">
                                @csrf
                                <span class="fi fi-{{ array_search(app()->getLocale(), config('app.locales')) }} me-1" style="font-size: 1rem;" title="{{ __('Language') }}"></span>
                                <select name="language" class="form-select form-select-sm border-0 bg-transparent px-2 py-1 fs-14" style="width: auto; max-width: 5rem;" onchange="if(this.value !== '{{ app()->getLocale() }}') this.form.submit();" title="{{ __('Language') }}">
                                    @foreach (config('app.locales') as $key => $locale)
                                        <option value="{{ $locale }}" {{ app()->getLocale() == $locale ? 'selected' : '' }}>{{ strtoupper($locale) }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @php($employeeUser = Auth::guard('employees')->user())
                            <div class="dropdown d-flex profile-1">
                                <a href="#" data-bs-toggle="dropdown" class="nav-link leading-none d-flex admin-header-user">
                                    <span class="admin-header-user-name"><i class="fe fe-user me-1"></i>{{ $employeeUser->name ?? __('Admin') }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <div class="drop-heading">
                                        <div class="text-center">
                                            <h5 class="admin-dropdown-heading mb-0 fs-14 fw-semibold">{{ $employeeUser->name ?? __('Admin') }}</h5>
                                            <small class="text-muted">Administrator</small>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider m-0"></div>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="$('#logoutForm').submit()">
                                        <i class="dropdown-icon fe fe-alert-circle"></i> Abmelden
                                    </a>
                                    <form id="logoutForm" method="POST" action="{{ route('admin.auth.logout') }}">@csrf</form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /app-Header -->
