<article class="tc-card tc-product" x-ref="product">
    <div class="tc-product__media">
        @if ($product['image'])
            <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" width="104" height="104" decoding="async" fetchpriority="high">
        @else
            @include('pages.modern-checkout.partials.icon', ['name' => 'image', 'size' => 28, 'stroke' => 1.4])
        @endif
    </div>
    <div class="tc-product__body">
        <h2 class="tc-product__title">{{ $product['title'] }}</h2>
        <ul class="tc-product__meta">
            @if ($product['location'] !== '')
                <li>@include('pages.modern-checkout.partials.icon', ['name' => 'pin', 'size' => 14, 'stroke' => 1.6])<span>{{ $product['location'] }}</span></li>
            @endif
            @if ($product['duration'])
                <li>@include('pages.modern-checkout.partials.icon', ['name' => 'clock', 'size' => 14, 'stroke' => 1.6])<span>{{ $product['duration'] }}</span></li>
            @endif
        </ul>
    </div>
</article>
