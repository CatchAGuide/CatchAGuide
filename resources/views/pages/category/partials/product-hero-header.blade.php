@include('pages.category.partials.hero-header', [
    'listingTitle' => $listingTitle,
    'listingSubtitle' => $listingSubtitle ?? '',
    'titleTag' => 'p',
    'searchAction' => $searchAction ?? listing_search_action(),
    'breadcrumbItems' => $breadcrumbItems ?? [],
])
