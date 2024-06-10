<script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "ItemList",
      "name": "Fishing Guide Directory",
      "description": "Browse a directory of fishing guides in your area.",
      "url": "https://forestry.com/fishing-guides",
      "itemListElement": [
        @foreach($allguidings as $guiding)
        {
          "@type": "ListItem",
          "position": 1,
          "item": {
            "@type": "Person",
            "name": "John Fisher",
            "url": "https://forestry.com/fishing-guides/john-fisher",
            "image": "https://forestry.com/images/john-fisher.jpg", // Image URL
            "description": "Experienced fishing guide with great reviews.",
            "aggregateRating": {
              "@type": "AggregateRating",
              "ratingValue": "4.8",
              "reviewCount": "20"
            },
            "offers": {
              "@type": "Offer",
              "price": "150",
              "priceCurrency": "EUR"
            }
          }
        },
        @endforeach
      ]
    }
</script>