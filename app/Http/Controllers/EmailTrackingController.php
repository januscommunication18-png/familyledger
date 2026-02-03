<?php

namespace App\Http\Controllers;

use App\Models\Backoffice\DripEmailLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

class EmailTrackingController extends Controller
{
    /**
     * Track email open via tracking pixel.
     * Returns a 1x1 transparent GIF image.
     */
    public function trackOpen(string $token): Response
    {
        $log = DripEmailLog::where('tracking_token', $token)->first();

        if ($log) {
            $log->markAsOpened();
        }

        // Return a 1x1 transparent GIF
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Mon, 01 Jan 1990 00:00:00 GMT');
    }

    /**
     * Track email link click and redirect to the original URL.
     */
    public function trackClick(Request $request, string $token): RedirectResponse
    {
        $log = DripEmailLog::where('tracking_token', $token)->first();

        if ($log) {
            $log->markAsClicked();
        }

        $url = $request->query('url', config('app.url'));

        // Validate URL to prevent open redirect vulnerability
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = config('app.url');
        }

        // Only allow http/https protocols
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            $url = config('app.url');
        }

        return redirect()->away($url);
    }
}
