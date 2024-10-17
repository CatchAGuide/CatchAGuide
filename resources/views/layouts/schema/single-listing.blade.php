<script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "Service",
      "serviceType": "Fishing Guiding",
      "provider": {
        "@type": "LocalBusiness",
        "name": "{{ config('app.name') }}",
        "address": "{{ $guiding->location }}"
      },
      "name": "{{ $guiding->title }}",
      "description": "{{ $guiding->description }}",
      "offers": {
        "@type": "Offer",
        "priceCurrency": "EUR",
        "price": "{{ $guiding->price }}",
        "availability": "http://schema.org/InStock"
      },
      "provider": {
        "@type": "Person",
        "name": "{{ $guiding->user->firstname }}"
      },
      "url": "{{ route('guidings.show', [$guiding->id,$guiding->slug]) }}"
    }
</script>