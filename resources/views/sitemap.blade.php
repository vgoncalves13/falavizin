<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- Global pages --}}
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- Per-neighborhood pages --}}
    @foreach ($neighborhoods as $neighborhood)
        @php $params = $neighborhood->routeParameters(); @endphp
        <url>
            <loc>{{ route('neighborhood.home', $params) }}</loc>
            <changefreq>daily</changefreq>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>{{ route('neighborhood.feed.index', $params) }}</loc>
            <changefreq>hourly</changefreq>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>{{ route('neighborhood.businesses.index', $params) }}</loc>
            <changefreq>daily</changefreq>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>{{ route('neighborhood.promotions.index', $params) }}</loc>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
        <url>
            <loc>{{ route('neighborhood.events.index', $params) }}</loc>
            <changefreq>daily</changefreq>
            <priority>0.7</priority>
        </url>
        <url>
            <loc>{{ route('neighborhood.pulso.index', $params) }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    {{-- Categories --}}
    @foreach ($categories as $category)
    <url>
        <loc>{{ route('categories.show', $category) }}</loc>
        <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Posts --}}
    @foreach ($posts as $post)
    <url>
        <loc>{{ $post->canonicalUrl() }}</loc>
        <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

    {{-- Negócios --}}
    @foreach ($businesses as $business)
    <url>
        <loc>{{ $business->canonicalUrl() }}</loc>
        <lastmod>{{ $business->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach
</urlset>
