<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
@php use App\Support\Seo; @endphp
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $settings['blog_title'] }}</title>
        <link>{{ Seo::abs(route('blog.index')) }}</link>
        <description>{{ $settings['blog_description'] }}</description>
        <language>ar-eg</language>
        <atom:link href="{{ Seo::abs(route('feed')) }}" rel="self" type="application/rss+xml"/>
@foreach($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ Seo::abs($post->url()) }}</link>
            <guid isPermaLink="true">{{ Seo::abs($post->url()) }}</guid>
            <pubDate>{{ $post->published_at?->toRfc2822String() }}</pubDate>
            <description>{{ $post->metaDescription() }}</description>
@if($post->author)
            <author>{{ $post->author->name }}</author>
@endif
        </item>
@endforeach
    </channel>
</rss>
