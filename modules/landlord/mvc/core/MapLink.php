<?php
declare(strict_types=1);

namespace BoardNest\Core;

/**
 * Safe Google Maps links for Landlord properties.
 *
 * The browser sends the SHARED LINK; server validates HTTPS + an exact
 * Google Maps host/path. Do not blindly trust URLs or resolve shortened links
 * from the PHP server (that can introduce server-side request forgery).
 *
 * When a long URL explicitly embeds an actual pin (`?q=lat,lng`,
 * `?query=lat,lng` or `!3dlat!4dlng`), coordinates may be copied for
 * legacy Field Agent GPS consumers. `@lat,lng` is only a map CAMERA centre
 * and must NOT be mistaken for the property pin.
 *
 * Short maps.app.goo.gl URLs cannot reveal coordinates without an external
 * redirect/geocoding step; they are saved as a link with coordinates NULL.
 */
final class MapLink {
    public static function normalize(string $raw): string {
        $url = trim($raw);
        if ($url === '') {
            throw new \DomainException('Paste the Google Maps share link for your boarding house.');
        }
        if (strlen($url) > 500) {
            throw new \DomainException('Google Maps link is too long (500 characters maximum).');
        }
        if (preg_match('/[\x00-\x20\x7f]/', $url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \DomainException('Enter a valid HTTPS Google Maps share link.');
        }
        $parts = parse_url($url);
        if (!is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            throw new \DomainException('Only HTTPS Google Maps links are accepted.');
        }
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $short = ($host === 'maps.app.goo.gl' && strlen(trim($path, '/')) > 0)
              || ($host === 'goo.gl' && str_starts_with($path, '/maps/') && strlen(substr($path, 6)) > 0);
        $googleHosts = [
            'google.com', 'www.google.com', 'maps.google.com',
            'google.lk', 'www.google.lk', 'maps.google.lk',
            'google.co.in', 'www.google.co.in', 'maps.google.co.in',
        ];
        $normal = in_array($host, $googleHosts, true)
            && (($path === '/maps' || str_starts_with($path, '/maps/'))
                || (str_starts_with($host, 'maps.google.') && ($path === '' || $path === '/' || $path === '/maps')));
        if (!$short && !$normal) {
            throw new \DomainException('Use Google Maps > Share > Copy link (maps.app.goo.gl or google.com/maps).');
        }
        return $url;
    }

    /** @return array{0:?string,1:?string} Coordinates only for explicit pin URLs. */
    public static function pinCoordinates(string $url): array {
        // Always revalidate: future callers must not pass arbitrary URLs.
        $url = self::normalize($url);
        $query = parse_url($url, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str($query, $params);
            foreach (['q', 'query', 'll'] as $key) {
                if (!isset($params[$key]) || !is_string($params[$key])) continue;
                $result = self::readPair($params[$key]);
                if ($result !== null) return $result;
            }
        }
        // Google's !3d..!4d.. notation marks a place pin in many full URLs.
        if (preg_match('/!3d([+-]?[0-9]+(?:\.[0-9]+)?)!4d([+-]?[0-9]+(?:\.[0-9]+)?)/', $url, $m)) {
            $result = self::readPair($m[1].','.$m[2]);
            if ($result !== null) return $result;
        }
        return [null, null];
    }

    /** @return array{0:string,1:string}|null */
    private static function readPair(string $value): ?array {
        if (!preg_match('/^\s*([+-]?[0-9]+(?:\.[0-9]+)?)\s*,\s*([+-]?[0-9]+(?:\.[0-9]+)?)\s*$/', $value, $m)) {
            return null;
        }
        $lat = (float)$m[1]; $lon = (float)$m[2];
        if (!is_finite($lat) || !is_finite($lon) || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return null;
        }
        return [$m[1], $m[2]];
    }
}
