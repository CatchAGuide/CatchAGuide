<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.strategy.supply-gaps') ? 'active' : '' }}" href="{{ route('admin.strategy.supply-gaps') }}">Supply gaps</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.strategy.content-coverage') ? 'active' : '' }}" href="{{ route('admin.strategy.content-coverage') }}">Content coverage</a>
    </li>
</ul>
