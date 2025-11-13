<?php
class Security {
    private $encryptionKey;
    private $jwtSecret;

    public function __construct() {
        $this->encryptionKey = getenv('ENCRYPTION_KEY') ?: 'default_encryption_key_32_chars_long';
        $this->jwtSecret = getenv('JWT_SECRET') ?: 'default_jwt_secret_32_chars_long';
    }

    public function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decrypt($encryptedData) {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
    }

    public function generateAPIKey() {
        return bin2hex(random_bytes(32));
    }

    public function validateAPIKey($apiKey) {
        // Check if API key exists in database
        $db = new Database();
        $stmt = $db->pdo->prepare("SELECT id FROM users WHERE api_key = ? AND is_active = 1");
        $stmt->execute([$apiKey]);
        return $stmt->fetch() !== false;
    }

    public function rateLimitCheck($identifier, $maxRequests = 60, $timeWindow = 60) {
        $key = "rate_limit:{$identifier}";
        // Simple file-based rate limiting (in production, use Redis)
        $file = sys_get_temp_dir() . "/{$key}.txt";

        $now = time();
        $requests = [];

        if (file_exists($file)) {
            $requests = json_decode(file_get_contents($file), true) ?: [];
            // Remove old requests outside the time window
            $requests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
                return ($now - $timestamp) < $timeWindow;
            });
        }

        if (count($requests) >= $maxRequests) {
            return false; // Rate limit exceeded
        }

        $requests[] = $now;
        file_put_contents($file, json_encode($requests));
        return true;
    }

    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function generateJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $headerEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        $payload['iat'] = time();
        $payload['exp'] = time() + (24 * 60 * 60); // 24 hours
        $payloadEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $this->jwtSecret, true);
        $signatureEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
    }

    public function validateJWT($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return false;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, $this->jwtSecret, true);
        $expectedSignatureEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));

        if ($signature !== $expectedSignatureEncoded) {
            return false;
        }

        $payloadDecoded = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        if ($payloadDecoded['exp'] < time()) {
            return false;
        }

        return $payloadDecoded;
    }

    public function logSecurityEvent($event, $data = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data' => $data
        ];

        $logFile = __DIR__ . '/../revenue_logs/security_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
    }

    public function isValidIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    public function getClientIP() {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            if (isset($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if ($this->isValidIP($ip)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }
}
?>
