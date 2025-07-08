<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate and return the sitemap XML
     */
    public function index(): Response
    {
        $baseUrl = config('app.url', 'https://bills.msar.me');
        $lastmod = now()->toISOString();

        $urls = [
            [
                'url' => $baseUrl,
                'lastmod' => $lastmod,
                'changefreq' => 'monthly',
                'priority' => '1.0'
            ],
            [
                'url' => $baseUrl . '/login',
                'lastmod' => $lastmod,
                'changefreq' => 'monthly',
                'priority' => '0.8'
            ],
            [
                'url' => $baseUrl . '/register',
                'lastmod' => $lastmod,
                'changefreq' => 'monthly',
                'priority' => '0.8'
            ]
        ];

        $xml = $this->generateSitemapXml($urls);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate the XML content for the sitemap
     */
    private function generateSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['url'], ENT_XML1) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}