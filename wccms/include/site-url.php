<?php
/** Current site origin, including the browser's external port. */
function siteBaseUrl($fallback = '')
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Only accept a hostname/IP and optional port, never URL delimiters.
    if (!preg_match('/\A(?:[a-z0-9.-]+|\[[a-f0-9:]+\])(?::[0-9]{1,5})?\z/i', $host)) {
        return rtrim($fallback, '/');
    }
    $https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    return ($https ? 'https://' : 'http://') . $host;
}
