<script type="application/ld+json">
    {
      "@@context": "http://schema.org",
      "@@type": "Service",
      "serviceType": "Fishing Guiding",
      "provider": {
        "@@type": "LocalBusiness",
        "name": @json(config('app.name')),
        "address": @json($guiding->location)
      },
      "name": @json($guiding->title),
      "description": @json($guiding->description),
      "offers": {
        "@@type": "Offer",
        "priceCurrency": "EUR",
        "price": @json($guiding->price),
        "availability": "http://schema.org/InStock"
      },
      "provider": {
        "@@type": "Person",
        "name": @json($guiding->user->firstname)
      },
      "url": @json(route('guidings.show', [$guiding->id,$guiding->slug]))
    }
</script>