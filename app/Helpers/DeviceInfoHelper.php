<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * DeviceInfoHelper - Extract and analyze device/browser information
 * 
 * This helper class provides utilities for detecting and extracting
 * device, browser, and platform information from HTTP requests.
 * 
 * @package App\Helpers
 * @author Your Name
 * @version 1.0.0
 */
class DeviceInfoHelper
{
    /**
     * Cache duration for user agent parsing (in minutes)
     */
    private const CACHE_DURATION = 60;

    /**
     * Extract comprehensive device information from request
     * 
     * @param Request $request
     * @return array
     */
    public static function extractDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent() ?? 'Unknown';
        
        return [
            'platform' => self::detectPlatform($userAgent),
            'browser' => self::detectBrowser($userAgent),
            'browser_version' => self::detectBrowserVersion($userAgent),
            'operating_system' => self::detectOperatingSystem($userAgent),
            'device_type' => self::detectDeviceType($userAgent),
            'is_mobile' => self::isMobile($userAgent),
            'is_tablet' => self::isTablet($userAgent),
            'is_desktop' => self::isDesktop($userAgent),
            'is_bot' => self::isBot($userAgent),
            'screen_resolution' => $request->input('screen_resolution'),
            'timezone' => $request->input('timezone', 'Asia/Jakarta'),
            'language' => $request->getPreferredLanguage(['en', 'id', 'es', 'fr', 'de']),
            'user_agent' => $userAgent,
            'ip_address' => $request->ip(),
            'capabilities' => self::extractCapabilities($request),
            'fingerprint' => self::generateDeviceFingerprint($request),
            'extracted_at' => now()->toISOString()
        ];
    }

    /**
     * Extract browser information specifically
     * 
     * @param Request $request
     * @return array
     */
    public static function extractBrowserInfo(Request $request): array
    {
        $userAgent = $request->userAgent() ?? 'Unknown';
        
        return [
            'user_agent' => $userAgent,
            'browser' => self::detectBrowser($userAgent),
            'browser_version' => self::detectBrowserVersion($userAgent),
            'engine' => self::detectBrowserEngine($userAgent),
            'platform' => self::detectPlatform($userAgent),
            'is_mobile' => self::isMobile($userAgent),
            'is_webview' => self::isWebView($userAgent),
            'supports_javascript' => true, // Assume true since request came through
            'cookies_enabled' => $request->hasCookie('test') || $request->cookies->count() > 0
        ];
    }

    /**
     * Detect browser from user agent
     * 
     * @param string $userAgent
     * @return string
     */
    public static function detectBrowser(string $userAgent): string
    {
        // Cache browser detection for performance
        $cacheKey = 'browser_detection_' . md5($userAgent);
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function() use ($userAgent) {
            $userAgent = strtolower($userAgent);
            
            // Modern browsers (order matters!)
            if (str_contains($userAgent, 'edg/')) {
                return 'Microsoft Edge';
            }
            if (str_contains($userAgent, 'chrome/') && !str_contains($userAgent, 'edg/')) {
                return 'Google Chrome';
            }
            if (str_contains($userAgent, 'firefox/')) {
                return 'Mozilla Firefox';
            }
            if (str_contains($userAgent, 'safari/') && !str_contains($userAgent, 'chrome/')) {
                return 'Safari';
            }
            if (str_contains($userAgent, 'opera/') || str_contains($userAgent, 'opr/')) {
                return 'Opera';
            }
            
            // Mobile browsers
            if (str_contains($userAgent, 'ucbrowser/')) {
                return 'UC Browser';
            }
            if (str_contains($userAgent, 'samsungbrowser/')) {
                return 'Samsung Internet';
            }
            
            // Legacy browsers
            if (str_contains($userAgent, 'msie') || str_contains($userAgent, 'trident/')) {
                return 'Internet Explorer';
            }
            
            return 'Unknown';
        });
    }

    /**
     * Detect browser version
     * 
     * @param string $userAgent
     * @return string|null
     */
    public static function detectBrowserVersion(string $userAgent): ?string
    {
        $patterns = [
            'chrome' => '/chrome\/([0-9.]+)/i',
            'firefox' => '/firefox\/([0-9.]+)/i',
            'safari' => '/version\/([0-9.]+).*safari/i',
            'edge' => '/edg\/([0-9.]+)/i',
            'opera' => '/(?:opera|opr)\/([0-9.]+)/i',
            'ie' => '/(?:msie\s|trident.*rv:)([0-9.]+)/i'
        ];

        foreach ($patterns as $browser => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                return $matches[1] ?? null;
            }
        }

        return null;
    }

    /**
     * Detect browser engine
     * 
     * @param string $userAgent
     * @return string
     */
    public static function detectBrowserEngine(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'webkit')) {
            if (str_contains($userAgent, 'blink')) {
                return 'Blink';
            }
            return 'WebKit';
        }
        
        if (str_contains($userAgent, 'gecko')) {
            return 'Gecko';
        }
        
        if (str_contains($userAgent, 'trident')) {
            return 'Trident';
        }
        
        return 'Unknown';
    }

    /**
     * Detect platform/operating system
     * 
     * @param string $userAgent
     * @return string
     */
    public static function detectPlatform(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        // Mobile platforms
        if (str_contains($userAgent, 'android')) {
            return 'Android';
        }
        if (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'iOS';
        }
        
        // Desktop platforms
        if (str_contains($userAgent, 'windows nt')) {
            return 'Windows';
        }
        if (str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'mac os x')) {
            return 'macOS';
        }
        if (str_contains($userAgent, 'linux')) {
            return 'Linux';
        }
        
        // Other platforms
        if (str_contains($userAgent, 'cros')) {
            return 'Chrome OS';
        }
        
        return 'Unknown';
    }

    /**
     * Detect operating system with version
     * 
     * @param string $userAgent
     * @return string
     */
    public static function detectOperatingSystem(string $userAgent): string
    {
        $patterns = [
            'windows' => [
                '/windows nt 10\.0/i' => 'Windows 10/11',
                '/windows nt 6\.3/i' => 'Windows 8.1',
                '/windows nt 6\.2/i' => 'Windows 8',
                '/windows nt 6\.1/i' => 'Windows 7',
                '/windows nt/i' => 'Windows'
            ],
            'mac' => [
                '/mac os x 10[._]15/i' => 'macOS Catalina+',
                '/mac os x 10[._]14/i' => 'macOS Mojave',
                '/mac os x 10[._]13/i' => 'macOS High Sierra',
                '/mac os x/i' => 'macOS'
            ],
            'ios' => [
                '/os ([0-9]+[._][0-9]+)/i' => 'iOS $1',
                '/iphone|ipad/i' => 'iOS'
            ],
            'android' => [
                '/android ([0-9.]+)/i' => 'Android $1',
                '/android/i' => 'Android'
            ]
        ];

        foreach ($patterns as $osType => $osPatterns) {
            foreach ($osPatterns as $pattern => $name) {
                if (preg_match($pattern, $userAgent, $matches)) {
                    return isset($matches[1]) ? str_replace('$1', $matches[1], $name) : $name;
                }
            }
        }

        return self::detectPlatform($userAgent);
    }

    /**
     * Detect device type
     * 
     * @param string $userAgent
     * @return string
     */
    public static function detectDeviceType(string $userAgent): string
    {
        if (self::isMobile($userAgent)) {
            return 'mobile';
        }
        
        if (self::isTablet($userAgent)) {
            return 'tablet';
        }
        
        if (self::isDesktop($userAgent)) {
            return 'desktop';
        }
        
        if (self::isBot($userAgent)) {
            return 'bot';
        }
        
        return 'unknown';
    }

    /**
     * Check if device is mobile
     * 
     * @param string $userAgent
     * @return bool
     */
    public static function isMobile(string $userAgent): bool
    {
        $mobilePatterns = [
            '/mobile/i',
            '/android/i',
            '/iphone/i',
            '/ipod/i',
            '/blackberry/i',
            '/windows phone/i',
            '/symbian/i',
            '/opera mini/i',
            '/palm/i'
        ];

        foreach ($mobilePatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if device is tablet
     * 
     * @param string $userAgent
     * @return bool
     */
    public static function isTablet(string $userAgent): bool
    {
        $tabletPatterns = [
            '/ipad/i',
            '/android(?!.*mobile)/i',
            '/tablet/i',
            '/kindle/i',
            '/silk/i',
            '/playbook/i'
        ];

        foreach ($tabletPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if device is desktop
     * 
     * @param string $userAgent
     * @return bool
     */
    public static function isDesktop(string $userAgent): bool
    {
        return !self::isMobile($userAgent) && !self::isTablet($userAgent) && !self::isBot($userAgent);
    }

    /**
     * Check if request is from a bot/crawler
     * 
     * @param string $userAgent
     * @return bool
     */
    public static function isBot(string $userAgent): bool
    {
        $botPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/googlebot/i',
            '/bingbot/i',
            '/facebookexternalhit/i',
            '/twitterbot/i',
            '/whatsapp/i',
            '/telegram/i'
        ];

        foreach ($botPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if browser is running in WebView
     * 
     * @param string $userAgent
     * @return bool
     */
    public static function isWebView(string $userAgent): bool
    {
        $webViewPatterns = [
            '/; wv\)/i',  // Android WebView
            '/webview/i',
            '/version\/.*mobile.*safari/i' // iOS WebView
        ];

        foreach ($webViewPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract device capabilities from request
     * 
     * @param Request $request
     * @return array
     */
    public static function extractCapabilities(Request $request): array
    {
        return [
            'javascript_enabled' => true, // Assume true if request came through
            'cookies_enabled' => $request->cookies->count() > 0,
            'local_storage' => $request->input('capabilities.localStorage', false),
            'session_storage' => $request->input('capabilities.sessionStorage', false),
            'geolocation' => $request->input('capabilities.geolocation', false),
            'touch_support' => self::hasTouchSupport($request->userAgent()),
            'screen_resolution' => $request->input('screen_resolution'),
            'color_depth' => $request->input('capabilities.colorDepth'),
            'timezone_offset' => $request->input('capabilities.timezoneOffset'),
            'language' => $request->getPreferredLanguage(),
            'languages' => $request->getLanguages(),
            'do_not_track' => $request->header('DNT') === '1'
        ];
    }

    /**
     * Check if device has touch support
     * 
     * @param string $userAgent
     * @return bool
     */
    public static function hasTouchSupport(string $userAgent): bool
    {
        return self::isMobile($userAgent) || self::isTablet($userAgent);
    }

    /**
     * Generate device fingerprint for tracking/security
     * 
     * @param Request $request
     * @return string
     */
    public static function generateDeviceFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->ip(),
            $request->input('screen_resolution', ''),
            $request->getPreferredLanguage(),
            $request->input('timezone', ''),
            $request->header('Accept-Encoding', ''),
            $request->header('Accept-Language', '')
        ];

        $fingerprint = implode('|', array_filter($components));
        
        return hash('sha256', $fingerprint);
    }

    /**
     * Get security risk assessment for device
     * 
     * @param Request $request
     * @return array
     */
    public static function getSecurityAssessment(Request $request): array
    {
        $userAgent = $request->userAgent() ?? '';
        $risk = 'low';
        $flags = [];

        // Check for suspicious patterns
        if (self::isBot($userAgent)) {
            $risk = 'medium';
            $flags[] = 'bot_detected';
        }

        if (empty($userAgent) || $userAgent === 'Unknown') {
            $risk = 'high';
            $flags[] = 'no_user_agent';
        }

        if (self::isOldBrowser($userAgent)) {
            $risk = 'medium';
            $flags[] = 'outdated_browser';
        }

        // Check IP patterns
        if (self::isSuspiciousIP($request->ip())) {
            $risk = 'high';
            $flags[] = 'suspicious_ip';
        }

        return [
            'risk_level' => $risk,
            'flags' => $flags,
            'score' => self::calculateRiskScore($flags),
            'is_trusted' => $risk === 'low' && empty($flags)
        ];
    }

    /**
     * Check if browser is outdated
     * 
     * @param string $userAgent
     * @return bool
     */
    private static function isOldBrowser(string $userAgent): bool
    {
        // Define minimum acceptable versions
        $minVersions = [
            'chrome' => 90,
            'firefox' => 85,
            'safari' => 14,
            'edge' => 90
        ];

        $browser = strtolower(self::detectBrowser($userAgent));
        $version = self::detectBrowserVersion($userAgent);

        if (!$version) {
            return true; // Unknown version = potentially old
        }

        $majorVersion = (int) explode('.', $version)[0];

        foreach ($minVersions as $browserName => $minVersion) {
            if (str_contains($browser, $browserName)) {
                return $majorVersion < $minVersion;
            }
        }

        return false;
    }

    /**
     * Check for suspicious IP patterns
     * 
     * @param string $ip
     * @return bool
     */
    private static function isSuspiciousIP(string $ip): bool
    {
        // Add your IP blacklist/validation logic here
        // This is a basic example
        $suspiciousPatterns = [
            '/^10\./',      // Private network (might be suspicious in some contexts)
            '/^192\.168\./', // Private network
            '/^172\.16\./',  // Private network
            '/^127\.0\.0\.1$/' // Localhost
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $ip)) {
                return false; // Actually, private IPs might be normal
            }
        }

        return false; // Implement your own logic
    }

    /**
     * Calculate risk score based on flags
     * 
     * @param array $flags
     * @return int
     */
    private static function calculateRiskScore(array $flags): int
    {
        $scores = [
            'bot_detected' => 30,
            'no_user_agent' => 50,
            'outdated_browser' => 20,
            'suspicious_ip' => 40
        ];

        $totalScore = 0;
        foreach ($flags as $flag) {
            $totalScore += $scores[$flag] ?? 10;
        }

        return min(100, $totalScore);
    }

    /**
     * Get readable device summary
     * 
     * @param Request $request
     * @return string
     */
    public static function getDeviceSummary(Request $request): string
    {
        $info = self::extractDeviceInfo($request);
        
        return sprintf(
            '%s %s on %s (%s)',
            $info['browser'],
            $info['browser_version'] ?: '',
            $info['operating_system'],
            $info['device_type']
        );
    }

    /**
     * Log device information for analytics
     * 
     * @param Request $request
     * @param string $event
     * @return void
     */
    public static function logDeviceInfo(Request $request, string $event = 'device_access'): void
    {
        try {
            $deviceInfo = self::extractDeviceInfo($request);
            $securityAssessment = self::getSecurityAssessment($request);
            
            Log::info("Device Info - {$event}", [
                'device_info' => $deviceInfo,
                'security' => $securityAssessment,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to log device info', [
                'error' => $e->getMessage(),
                'event' => $event
            ]);
        }
    }
}