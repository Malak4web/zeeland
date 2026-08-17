<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Setting;
use App\Support\Seo;

class LandingController extends Controller
{
    public function index()
    {
        $seo = Seo::make()
            ->title((string) Setting::get('meta_title'), suffix: false)
            ->description(Setting::get('meta_description'))
            ->canonical(Seo::origin().'/')
            ->image(Setting::get('og_image'))
            ->schema([
                '@type' => 'Product',
                'name' => 'Zeeland Frozen French Fries — Premium Straight Cut',
                'alternateName' => 'زيلاند بطاطس نص مقلية مجمّدة',
                'brand' => ['@type' => 'Brand', 'name' => 'Zeeland'],
                'category' => 'Frozen Potato Products',
                'material' => 'Santana potato variety',
                'weight' => ['@type' => 'QuantitativeValue', 'value' => 2.5, 'unitCode' => 'KGM'],
                'countryOfOrigin' => 'EG',
                'image' => Seo::origin().'/img/brand-kitchen.jpg',
                'description' => 'بطاطس نص مقلية مجمّدة قطع مستقيم من صنف سنتانا، سكريات مختزلة منخفضة ومادة جافة عالية. عبوة 2.5 كجم، تُحفظ على −18 درجة مئوية.',
            ])
            ->schema([
                '@type' => 'WebSite',
                '@id' => Seo::origin().'/#website',
                'url' => Seo::origin().'/',
                'name' => Setting::get('site_name'),
                'inLanguage' => 'ar-EG',
                'publisher' => ['@id' => Seo::origin().'/#organization'],
            ]);

        // Three most recent posts, linked from the footer: a landing page with
        // no path into the blog wastes every article it publishes.
        $latest = Post::query()->live()
            ->latest('published_at')
            ->take(3)
            ->get(['title', 'slug', 'excerpt', 'cover_image', 'published_at', 'reading_minutes']);

        return view('site.landing', compact('seo', 'latest'));
    }
}
