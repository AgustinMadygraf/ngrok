<?php
/*
Path: redirect.php
*/

header('Content-Type: application/json');
require_once __DIR__ . '/database.php';

class NgrokVerifier {
    private $url;
    private $logFile;

    public function __construct($url, $logFile = __DIR__ . '/ngrok_verifier.log') {
        $this->url = $url;
        $this->logFile = $logFile;
    }

    public function getRootUrl() {
        $scheme = parse_url($this->url, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($this->url, PHP_URL_HOST);
        return $scheme . '://' . $host;
    }

    public function isOnline() {
        $root = $this->getRootUrl();
        $ch = curl_init($root);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $rootContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Log para depuración
        $logMsg = date('Y-m-d H:i:s') . " | URL: $root | HTTP: $httpCode | cURLError: $curlError | Content: " . substr($rootContent, 0, 500) . "\n";
        file_put_contents($this->logFile, $logMsg, FILE_APPEND);

        $offlineCodes = [404, 502, 503, 504];
        if ($httpCode === 0 || in_array($httpCode, $offlineCodes) ||
            $rootContent === false ||
            strpos($rootContent, 'ERR_NGROK_3200') !== false) {
            return false;
        }
        return true;
    }
}

class RedirectResponse {
    public static function send($data) {
        echo json_encode($data);
        exit;
    }
}

$configPath = __DIR__ . '/env.php';
if (!file_exists($configPath)) {
    RedirectResponse::send(['url' => null, 'error' => 'Archivo de configuración no encontrado']);
}

$config = require $configPath;

try {
    $db = new Database();
    $latestUrl = $db->getLatestUrl($config['TABLE']);
    $db->close();

    $url = isset($_GET['url']) && $_GET['url'] !== '' ? $_GET['url'] : $latestUrl;

    if ($url) {
        $verifier = new NgrokVerifier($url);
        if (!$verifier->isOnline()) {
            RedirectResponse::send([
                'url' => null,
                'redirect' => 'form.html',
                'error' => 'El endpoint ngrok está offline o no disponible.'
            ]);
        }
        RedirectResponse::send(['url' => $url]);
    } else {
        RedirectResponse::send(['url' => null, 'redirect' => 'form.html']);
    }
} catch (Exception $e) {
    RedirectResponse::send(['url' => null, 'error' => $e->getMessage()]);
}
?>