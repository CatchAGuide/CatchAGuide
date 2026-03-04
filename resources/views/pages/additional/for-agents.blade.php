@extends('layouts.app-v2-1')

@section('title', 'Data for AI agents & developers')
@section('description', 'Machine-readable fishing trips and vacations catalog for AI agents, tools and developers.')

@section('content')
<section class="pt-5 pb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="mb-3 h3">Data for AI agents &amp; developers</h1>
                <p class="mb-4">
                    Catch a Guide is a marketplace for guided fishing trips, fishing holidays and fishing camps.
                    This page documents public, read-only endpoints that expose our catalog in a
                    machine-readable JSON format for AI assistants, tools and integrations.
                </p>

                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Unified trips catalog</h2>
                        <p class="mb-2">
                            The main endpoint returns both <strong>guidings</strong> and <strong>vacations</strong> in a unified schema:
                        </p>
                        <pre class="bg-light p-3 small mb-3"><code>GET {{ url('/api/catalog/trips') }}</code></pre>
                        <p class="mb-2">Response shape (simplified):</p>
<pre class="bg-light p-3 small mb-0"><code>{
  "version": "1.0",
  "generated_at": "ISO-8601 timestamp",
  "trips": [
    {
      "type": "guiding" | "vacation",
      "title": "Guided pike fishing in Sweden",
      "slug": "guided-pike-fishing-sweden",
      "url": "https://catchaguide.com/guidings/123/guided-pike-fishing-sweden",
      "language": "en",
      "country": "Sweden",
      "region": "Värmland",
      "city": "Karlstad",
      "categories": ["pike", "boat", "spin-fishing"],
      "min_price": 250.0,
      "currency": "EUR",
      "duration": "1 day",
      "availability_summary": "Months: April–October | Weekdays: Mon–Sun",
      "short_description": "Experience top-class pike fishing with an experienced local guide.",
      "images": ["/storage/guidings/123/main.jpg"],
      "included": ["Boat & fuel", "Fishing tackle", "Soft drinks"],
      "boat_type": "boat" | "shore" | "Aluminium boat",
      "fishing_type": "Spinning",
      "target_fish": ["pike", "zander"],
      "pricing": {
        "currency": "EUR",
        "min_price": 250.0,
        "tiers": [
          { "person": 1, "amount": 250.0 },
          { "person": 2, "amount": 320.0 }
        ],
        "extras": [
          { "name": "Extra hour", "price": 50.0 }
        ]
      }
    }
  ]
}</code></pre>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Per-type endpoints</h2>
                        <p class="mb-2">
                            You can also request only guidings or only vacations:
                        </p>
                        <ul class="mb-2">
                            <li><code>GET {{ url('/api/catalog/guidings') }}</code></li>
                            <li><code>GET {{ url('/api/catalog/vacations') }}</code></li>
                        </ul>
                        <p class="mb-0">
                            These return the same structure as the unified catalog, filtered to the respective type.
                        </p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Usage, rate limits &amp; privacy</h2>
                        <ul class="mb-0">
                            <li>The endpoints are <strong>read-only</strong> and expose only public trip data (no user or booking data).</li>
                            <li>Basic per-IP rate limiting is applied; please avoid aggressive polling.</li>
                            <li>When building AI tools, prefer using the canonical <code>url</code> field when returning links to users.</li>
                        </ul>
                    </div>
                </div>

                <p class="text-muted small mb-0">
                    If you build an AI integration or agent around these endpoints and need adjustments,
                    please contact us via the <a href="{{ route('additional.contact') }}">contact form</a>.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection

