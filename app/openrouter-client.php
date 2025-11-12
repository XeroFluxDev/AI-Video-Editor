<?php
/**
 * ============================================
 * OpenRouter Client - Portable Single File
 * ============================================
 *
 * Complete OpenRouter API client in a single portable file.
 * No external dependencies required - just include and use!
 *
 * @version     1.0.0
 * @author      XeroFluxDev
 * @license     MIT
 * @requires    PHP 8.1+
 * @link        https://github.com/XeroFluxDev/OpenrouterClient
 *
 * FEATURES:
 * - 22 OpenRouter API endpoints
 * - Modern PHP 8.1+ (type hints, readonly, named parameters)
 * - Fluent interface design
 * - Optional request/response logging
 * - Comprehensive error handling
 * - PSR-12 code style
 * - Zero external dependencies
 *
 * QUICK START:
 * ```php
 * require_once 'openrouter-client-portable.php';
 *
 * use OpenRouterClient\OpenRouterClient;
 *
 * $client = new OpenRouterClient('sk-or-v1-your-api-key');
 *
 * // Chat completion
 * $response = $client->completions()->chat(
 *     model: 'openai/gpt-4',
 *     messages: [
 *         ['role' => 'user', 'content' => 'Hello!']
 *     ]
 * );
 *
 * // List models
 * $models = $client->models()->list();
 *
 * // Get credits
 * $credits = $client->credits()->get();
 * ```
 *
 * CONFIGURATION:
 * ```php
 * use OpenRouterClient\{OpenRouterClient, Config};
 *
 * Config::init([
 *     'base_url' => 'https://openrouter.ai',
 *     'app_name' => 'My App',
 *     'app_url' => 'https://myapp.com',
 *     'enable_logging' => true,
 *     'logs_dir' => 'api-logs',
 *     'timeout' => 30,
 * ]);
 *
 * $client = new OpenRouterClient('sk-or-v1-xxx');
 * ```
 */

declare(strict_types=1);

namespace OpenRouterClient;

// ============================================
// SECTION 1: EXCEPTION CLASSES
// ============================================

/**
 * Base exception for all OpenRouter errors
 */
class OpenRouterException extends \Exception
{
    public function __construct(
        string $message = "",
        int $code = 0,
        public readonly ?array $context = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Thrown when input validation fails
 */
class ValidationException extends OpenRouterException
{
    public function __construct(string $message, ?array $context = null)
    {
        parent::__construct($message, 400, $context);
    }
}

/**
 * Thrown when API authentication fails (401)
 */
class AuthenticationException extends OpenRouterException
{
    public function __construct(string $message, ?array $context = null)
    {
        parent::__construct($message, 401, $context);
    }
}

/**
 * Thrown when a model is not found (404)
 */
class ModelNotFoundException extends OpenRouterException
{
    public function __construct(string $message, ?array $context = null)
    {
        parent::__construct($message, 404, $context);
    }
}

/**
 * Thrown when rate limit is exceeded (429)
 */
class RateLimitException extends OpenRouterException
{
    public function __construct(
        string $message,
        public readonly ?int $retryAfter = null,
        ?array $context = null
    ) {
        parent::__construct($message, 429, $context);
    }
}

/**
 * Thrown when credits are insufficient (402/403)
 */
class InsufficientCreditsException extends OpenRouterException
{
    public function __construct(string $message, ?array $context = null)
    {
        parent::__construct($message, 402, $context);
    }
}

// ============================================
// SECTION 2: CONFIGURATION MANAGEMENT
// ============================================

/**
 * Configuration management class
 *
 * Handles environment variables and configuration options.
 * Can load from .env files or be set programmatically.
 */
final class Config
{
    private static array $config = [];
    private static bool $initialized = false;

    /**
     * Initialize configuration with an array
     *
     * @param array<string, mixed> $config Configuration array
     * @return void
     */
    public static function init(array $config = []): void
    {
        self::$config = array_merge(self::$config, $config);
        self::$initialized = true;
    }

    /**
     * Get a configuration value
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        // Check config array first
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        // Fall back to environment variable
        $envValue = getenv($key);
        if ($envValue !== false) {
            return $envValue;
        }

        return $default;
    }

    /**
     * Set a configuration value
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        self::$config[$key] = $value;
    }

    /**
     * Check if a configuration key exists
     *
     * @param string $key Configuration key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$config[$key]) || getenv($key) !== false;
    }

    /**
     * Load configuration from .env file
     *
     * @param string $filePath Path to .env file
     * @return void
     */
    public static function loadEnv(string $filePath): void
    {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                self::$config[$key] = $value;
            }
        }

        self::$initialized = true;
    }

    /**
     * Get all configuration values
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return self::$config;
    }

    /**
     * Reset configuration (mainly for testing)
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$config = [];
        self::$initialized = false;
    }
}

// ============================================
// SECTION 3: VALIDATION UTILITIES
// ============================================

/**
 * Input validation utilities
 *
 * All methods are static for easy access.
 * Throws ValidationException on validation failure.
 */
final class Validator
{
    /**
     * Validate that required fields are present
     *
     * @param array<string, mixed> $data Data array
     * @param array<string> $fields Required field names
     * @return void
     * @throws ValidationException
     */
    public static function required(array $data, array $fields): void
    {
        $missing = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new ValidationException('Missing required fields: ' . implode(', ', $missing));
        }
    }

    /**
     * Validate API key format
     *
     * @param string $auth API key
     * @return void
     * @throws ValidationException
     */
    public static function auth(string $auth): void
    {
        if (empty($auth)) {
            throw new ValidationException('API key is required');
        }

        // Basic format check for OpenRouter keys
        if (!preg_match('/^sk-or-(v1|prov)-[a-zA-Z0-9]+$/', $auth)) {
            // Allow other formats but check minimum length
            if (strlen($auth) < 10) {
                throw new ValidationException('API key appears to be invalid (too short)');
            }
        }
    }

    /**
     * Validate model string
     *
     * @param string $model Model identifier
     * @return void
     * @throws ValidationException
     */
    public static function model(string $model): void
    {
        if (empty($model)) {
            throw new ValidationException('Model must be a non-empty string');
        }
    }

    /**
     * Validate chat messages array
     *
     * @param array<array<string, mixed>> $messages Messages array
     * @return void
     * @throws ValidationException
     */
    public static function messages(array $messages): void
    {
        if (empty($messages)) {
            throw new ValidationException('Messages must be a non-empty array');
        }

        foreach ($messages as $index => $message) {
            if (!is_array($message)) {
                throw new ValidationException("Message at index $index must be an array");
            }

            if (!isset($message['role']) || !isset($message['content'])) {
                throw new ValidationException("Message at index $index must have 'role' and 'content' fields");
            }

            self::enum(
                $message['role'],
                ['system', 'user', 'assistant', 'tool'],
                "messages[$index].role"
            );
        }
    }

    /**
     * Validate tools array
     *
     * @param array<array<string, mixed>> $tools Tools array
     * @return void
     * @throws ValidationException
     */
    public static function tools(array $tools): void
    {
        foreach ($tools as $index => $tool) {
            if (!is_array($tool)) {
                throw new ValidationException("Tool at index $index must be an array");
            }

            if (!isset($tool['type'])) {
                throw new ValidationException("Tool at index $index must have a 'type' field");
            }

            if ($tool['type'] === 'function') {
                if (!isset($tool['function'])) {
                    throw new ValidationException("Function tool at index $index must have a 'function' field");
                }

                if (!isset($tool['function']['name'])) {
                    throw new ValidationException("Function tool at index $index must have a 'function.name' field");
                }
            }
        }
    }

    /**
     * Validate prompt (string or array)
     *
     * @param string|array<string> $prompt Prompt
     * @return void
     * @throws ValidationException
     */
    public static function prompt(string|array $prompt): void
    {
        if (is_string($prompt) && empty($prompt)) {
            throw new ValidationException('Prompt cannot be empty');
        }

        if (is_array($prompt) && empty($prompt)) {
            throw new ValidationException('Prompt array cannot be empty');
        }
    }

    /**
     * Validate enum value
     *
     * @param mixed $value Value to check
     * @param array<mixed> $allowed Allowed values
     * @param string $fieldName Field name for error message
     * @return void
     * @throws ValidationException
     */
    public static function enum(mixed $value, array $allowed, string $fieldName): void
    {
        if ($value !== null && !in_array($value, $allowed, true)) {
            throw new ValidationException(
                sprintf(
                    'Invalid value for %s: "%s". Allowed values: %s',
                    $fieldName,
                    (string)$value,
                    implode(', ', $allowed)
                )
            );
        }
    }

    /**
     * Validate numeric range
     *
     * @param float $value Value to check
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @param string $fieldName Field name for error message
     * @return void
     * @throws ValidationException
     */
    public static function range(float $value, float $min, float $max, string $fieldName): void
    {
        if (!is_numeric($value)) {
            throw new ValidationException("$fieldName must be a number");
        }

        if ($value < $min || $value > $max) {
            throw new ValidationException("$fieldName must be between $min and $max");
        }
    }

    /**
     * Validate positive integer
     *
     * @param int $value Value to check
     * @param string $fieldName Field name for error message
     * @return void
     * @throws ValidationException
     */
    public static function positiveInteger(int $value, string $fieldName): void
    {
        if ($value <= 0) {
            throw new ValidationException("$fieldName must be greater than 0");
        }
    }

    /**
     * Validate string with optional length constraints
     *
     * @param string $value Value to check
     * @param string $fieldName Field name for error message
     * @param int $minLength Minimum length (default: 0)
     * @param ?int $maxLength Maximum length (default: null)
     * @return void
     * @throws ValidationException
     */
    public static function string(string $value, string $fieldName, int $minLength = 0, ?int $maxLength = null): void
    {
        $length = strlen($value);

        if ($minLength > 0 && $length < $minLength) {
            throw new ValidationException("$fieldName must be at least $minLength characters");
        }

        if ($maxLength !== null && $length > $maxLength) {
            throw new ValidationException("$fieldName must be at most $maxLength characters");
        }
    }
}

// ============================================
// SECTION 4: LOGGING SYSTEM
// ============================================

/**
 * API request/response logging system
 *
 * Logs complete request/response pairs to organized daily folders.
 * Optional feature controlled by configuration.
 */
final class Logger
{
    private bool $enabled;
    private string $logsDir;
    private int $retentionDays;
    private bool $maskKeys;

    /**
     * Create a new Logger instance
     *
     * @param array<string, mixed> $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->enabled = ($config['enable_logging'] ?? Config::get('ENABLE_API_LOGGING', 'false')) === 'true'
            || ($config['enable_logging'] ?? false) === true;
        $this->logsDir = $config['logs_dir'] ?? Config::get('API_LOGS_DIR', 'api-logs');
        $this->retentionDays = (int)($config['logs_retention_days'] ?? Config::get('API_LOGS_RETENTION_DAYS', 30));
        $this->maskKeys = ($config['mask_api_keys'] ?? Config::get('API_LOGS_MASK_KEYS', 'true')) === 'true'
            || ($config['mask_api_keys'] ?? true) === true;
    }

    /**
     * Check if logging is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Log an API request/response pair
     *
     * @param string $generationId Unique generation ID
     * @param array<string, mixed> $request Request data
     * @param array<string, mixed> $response Response data
     * @param array<string, mixed> $metadata Optional metadata
     * @return ?string File path where log was saved, or null if logging disabled
     */
    public function log(
        string $generationId,
        array $request,
        array $response,
        array $metadata = []
    ): ?string {
        if (!$this->enabled) {
            return null;
        }

        // Mask sensitive data if configured
        if ($this->maskKeys) {
            $request = $this->maskSensitiveData($request);
            $metadata = $this->maskSensitiveData($metadata);
        }

        $logEntry = [
            'generation_id' => $generationId,
            'trace_id' => $metadata['trace_id'] ?? $this->generateTraceId(),
            'logged_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'request' => [
                'endpoint' => $request['endpoint'] ?? null,
                'method' => $request['method'] ?? 'POST',
                'headers' => $request['headers'] ?? [],
                'body' => $request['body'] ?? null,
                'timestamp' => $request['timestamp'] ?? time(),
                'iso_timestamp' => isset($request['timestamp'])
                    ? gmdate('Y-m-d\TH:i:s\Z', $request['timestamp'])
                    : gmdate('Y-m-d\TH:i:s\Z'),
            ],
            'response' => [
                'status_code' => $response['status_code'] ?? null,
                'headers' => $response['headers'] ?? [],
                'body' => $response['body'] ?? null,
                'duration_ms' => $response['duration_ms'] ?? 0,
                'timestamp' => $response['timestamp'] ?? time(),
                'iso_timestamp' => isset($response['timestamp'])
                    ? gmdate('Y-m-d\TH:i:s\Z', $response['timestamp'])
                    : gmdate('Y-m-d\TH:i:s\Z'),
            ],
            'metadata' => array_merge([
                'php_version' => phpversion(),
                'memory_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2),
            ], $metadata)
        ];

        $filePath = $this->getLogFilePath($generationId);

        // Atomic write
        $jsonData = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($jsonData === false) {
            return null;
        }

        $tempFile = $filePath . '.tmp';

        if (file_put_contents($tempFile, $jsonData, LOCK_EX) !== false) {
            rename($tempFile, $filePath);
            return $filePath;
        }

        return null;
    }

    /**
     * Read a logged request/response pair
     *
     * @param string $generationId Generation ID
     * @param ?string $date Date in YYYY-MM-DD format (default: today)
     * @return ?array<string, mixed> Log data or null if not found
     */
    public function read(string $generationId, ?string $date = null): ?array
    {
        $filePath = $this->getLogFilePath($generationId, $date);

        if (!file_exists($filePath)) {
            // Try today's date if no date specified
            if ($date === null) {
                return null;
            }

            // Try today
            $filePath = $this->getLogFilePath($generationId, date('Y-m-d'));
            if (!file_exists($filePath)) {
                return null;
            }
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            return null;
        }

        return json_decode($contents, true);
    }

    /**
     * List all logs for a specific date
     *
     * @param string $date Date in YYYY-MM-DD format
     * @param int $limit Maximum number of results
     * @return array<string> Array of generation IDs
     */
    public function list(string $date, int $limit = 100): array
    {
        $dayDir = $this->logsDir . '/' . $date;

        if (!is_dir($dayDir)) {
            return [];
        }

        $files = glob($dayDir . '/*.json');
        if ($files === false) {
            return [];
        }

        $files = array_slice($files, 0, $limit);

        $generationIds = [];
        foreach ($files as $file) {
            $basename = basename($file, '.json');
            $generationIds[] = $basename;
        }

        return $generationIds;
    }

    /**
     * Clean up old log files
     *
     * @param ?int $daysToKeep Number of days to keep (default: from config)
     * @return int Number of files deleted
     */
    public function cleanup(?int $daysToKeep = null): int
    {
        $daysToKeep = $daysToKeep ?? $this->retentionDays;
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deletedCount = 0;

        if (!is_dir($this->logsDir)) {
            return 0;
        }

        $dayDirs = glob($this->logsDir . '/*', GLOB_ONLYDIR);
        if ($dayDirs === false) {
            return 0;
        }

        foreach ($dayDirs as $dayDir) {
            $dayName = basename($dayDir);
            $dayTime = strtotime($dayName);

            if ($dayTime !== false && $dayTime < $cutoffTime) {
                // Delete all files in this day's directory
                $files = glob($dayDir . '/*.json');
                if ($files !== false) {
                    foreach ($files as $file) {
                        if (unlink($file)) {
                            $deletedCount++;
                        }
                    }
                }

                // Remove the directory if empty
                $remaining = scandir($dayDir);
                if ($remaining !== false && count($remaining) === 2) { // Only . and ..
                    rmdir($dayDir);
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Mask sensitive data (API keys)
     *
     * @param array<string, mixed> $data Data to mask
     * @return array<string, mixed>
     */
    private function maskSensitiveData(array $data): array
    {
        $masked = $data;

        // Mask API keys
        if (isset($masked['auth'])) {
            $masked['auth'] = $this->maskApiKey($masked['auth']);
        }

        if (isset($masked['headers']['Authorization'])) {
            $key = str_replace('Bearer ', '', $masked['headers']['Authorization']);
            $masked['headers']['Authorization'] = 'Bearer ' . $this->maskApiKey($key);
        }

        return $masked;
    }

    /**
     * Mask API key for logging
     *
     * @param string $key API key
     * @return string Masked key
     */
    private function maskApiKey(string $key): string
    {
        if (strlen($key) < 10) {
            return '***';
        }

        $prefix = substr($key, 0, 7);
        $suffix = substr($key, -3);
        return $prefix . '***' . $suffix;
    }

    /**
     * Get log file path for a generation ID
     *
     * @param string $generationId Generation ID
     * @param ?string $date Date in YYYY-MM-DD format (default: today)
     * @return string Full file path
     */
    private function getLogFilePath(string $generationId, ?string $date = null): string
    {
        $date = $date ?? date('Y-m-d');
        $dayDir = $this->logsDir . '/' . $date;

        // Create directory if it doesn't exist
        if (!is_dir($dayDir)) {
            mkdir($dayDir, 0755, true);
        }

        return $dayDir . '/' . $generationId . '.json';
    }

    /**
     * Generate a unique trace ID
     *
     * @return string
     */
    private function generateTraceId(): string
    {
        return 'req_' . bin2hex(random_bytes(12));
    }

    /**
     * Generate a unique generation ID
     *
     * @return string
     */
    public static function generateGenerationId(): string
    {
        return 'gen_' . bin2hex(random_bytes(16));
    }
}

// ============================================
// SECTION 5: HTTP CLIENT
// ============================================

/**
 * HTTP client for making requests to OpenRouter API
 *
 * Handles all HTTP communication, error handling, and optional logging.
 */
final class HttpClient
{
    private string $baseUrl;
    private int $timeout;
    private ?string $appName;
    private ?string $appUrl;
    private ?Logger $logger;

    /**
     * Create a new HttpClient instance
     *
     * @param array<string, mixed> $config Configuration options
     * @param ?Logger $logger Optional logger instance
     */
    public function __construct(array $config = [], ?Logger $logger = null)
    {
        $this->baseUrl = $config['base_url'] ?? Config::get('OPENROUTER_BASE_URL', 'https://openrouter.ai');
        $this->timeout = (int)($config['timeout'] ?? Config::get('HTTP_TIMEOUT', 30));
        $this->appName = $config['app_name'] ?? Config::get('OPENROUTER_APP_NAME');
        $this->appUrl = $config['app_url'] ?? Config::get('OPENROUTER_APP_URL');
        $this->logger = $logger;
    }

    /**
     * Make an HTTP request to OpenRouter API
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array<string, mixed> $data Request body data
     * @param string $auth API key
     * @param array<string, string> $customHeaders Additional headers
     * @return array<string, mixed> Response data
     * @throws OpenRouterException
     */
    public function request(
        string $endpoint,
        string $method,
        array $data,
        string $auth,
        array $customHeaders = []
    ): array {
        $url = $this->baseUrl . $endpoint;
        $startTime = microtime(true);
        $startTimestamp = time();

        // Prepare headers
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $auth,
        ];

        if ($this->appName) {
            $headers[] = 'X-Title: ' . $this->appName;
        }

        if ($this->appUrl) {
            $headers[] = 'HTTP-Referer: ' . $this->appUrl;
        }

        // Merge custom headers
        foreach ($customHeaders as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        // Initialize cURL
        $ch = curl_init();
        if ($ch === false) {
            throw new OpenRouterException('Failed to initialize cURL');
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
        ]);

        // Add request body for POST/PATCH/PUT
        if (in_array($method, ['POST', 'PATCH', 'PUT']) && !empty($data)) {
            $jsonData = json_encode($data);
            if ($jsonData === false) {
                throw new OpenRouterException('Failed to encode request data as JSON');
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }

        // Execute request
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        curl_close($ch);

        // Calculate duration
        $durationMs = round((microtime(true) - $startTime) * 1000);
        $endTimestamp = time();

        // Parse headers and body
        $responseHeaders = [];
        $responseBody = null;

        if ($response !== false) {
            $headerText = substr($response, 0, $headerSize);
            $bodyText = substr($response, $headerSize);

            // Parse headers
            $headerLines = explode("\r\n", $headerText);
            foreach ($headerLines as $line) {
                if (str_contains($line, ':')) {
                    [$key, $value] = explode(':', $line, 2);
                    $responseHeaders[trim($key)] = trim($value);
                }
            }

            // Parse body
            if (!empty($bodyText)) {
                $decoded = json_decode($bodyText, true);
                $responseBody = ($decoded !== null) ? $decoded : $bodyText;
            }
        }

        // Extract generation ID if present
        $generationId = $responseHeaders['X-OpenRouter-Generation-Id']
            ?? (is_array($responseBody) ? ($responseBody['id'] ?? null) : null)
            ?? Logger::generateGenerationId();

        // Log request/response if logger enabled
        if ($this->logger?->isEnabled()) {
            $this->logger->log(
                $generationId,
                [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'headers' => $this->parseHeadersArray($headers),
                    'body' => $data,
                    'auth' => $auth,
                    'timestamp' => $startTimestamp,
                ],
                [
                    'status_code' => $httpCode,
                    'headers' => $responseHeaders,
                    'body' => $responseBody,
                    'duration_ms' => $durationMs,
                    'timestamp' => $endTimestamp,
                ],
                [
                    'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'OpenRouter-PHP-Client/1.0',
                ]
            );
        }

        // Handle cURL errors
        if ($response === false) {
            throw new OpenRouterException('HTTP request failed: ' . $error);
        }

        // Handle HTTP errors
        if ($httpCode >= 400) {
            $this->handleHttpError($httpCode, $responseBody, $responseHeaders);
        }

        return is_array($responseBody) ? $responseBody : ['data' => $responseBody];
    }

    /**
     * Make a GET request
     *
     * @param string $endpoint API endpoint
     * @param string $auth API key
     * @param array<string, string> $customHeaders Additional headers
     * @return array<string, mixed>
     * @throws OpenRouterException
     */
    public function get(string $endpoint, string $auth, array $customHeaders = []): array
    {
        return $this->request($endpoint, 'GET', [], $auth, $customHeaders);
    }

    /**
     * Make a POST request
     *
     * @param string $endpoint API endpoint
     * @param array<string, mixed> $data Request body
     * @param string $auth API key
     * @param array<string, string> $customHeaders Additional headers
     * @return array<string, mixed>
     * @throws OpenRouterException
     */
    public function post(string $endpoint, array $data, string $auth, array $customHeaders = []): array
    {
        return $this->request($endpoint, 'POST', $data, $auth, $customHeaders);
    }

    /**
     * Make a PATCH request
     *
     * @param string $endpoint API endpoint
     * @param array<string, mixed> $data Request body
     * @param string $auth API key
     * @param array<string, string> $customHeaders Additional headers
     * @return array<string, mixed>
     * @throws OpenRouterException
     */
    public function patch(string $endpoint, array $data, string $auth, array $customHeaders = []): array
    {
        return $this->request($endpoint, 'PATCH', $data, $auth, $customHeaders);
    }

    /**
     * Make a DELETE request
     *
     * @param string $endpoint API endpoint
     * @param string $auth API key
     * @param array<string, string> $customHeaders Additional headers
     * @return array<string, mixed>
     * @throws OpenRouterException
     */
    public function delete(string $endpoint, string $auth, array $customHeaders = []): array
    {
        return $this->request($endpoint, 'DELETE', [], $auth, $customHeaders);
    }

    /**
     * Handle HTTP error responses
     *
     * @param int $httpCode HTTP status code
     * @param mixed $responseBody Response body
     * @param array<string, string> $responseHeaders Response headers
     * @return void
     * @throws OpenRouterException
     */
    private function handleHttpError(int $httpCode, mixed $responseBody, array $responseHeaders): void
    {
        $errorMessage = 'HTTP ' . $httpCode;

        if (is_array($responseBody) && isset($responseBody['error'])) {
            $errorMessage = is_array($responseBody['error'])
                ? ($responseBody['error']['message'] ?? json_encode($responseBody['error']))
                : $responseBody['error'];
        } elseif (is_string($responseBody)) {
            $errorMessage = $responseBody;
        }

        $context = ['http_code' => $httpCode, 'response' => $responseBody];

        switch ($httpCode) {
            case 401:
                throw new AuthenticationException($errorMessage, $context);
            case 404:
                throw new ModelNotFoundException($errorMessage, $context);
            case 429:
                $retryAfter = isset($responseHeaders['Retry-After']) ? (int)$responseHeaders['Retry-After'] : null;
                throw new RateLimitException($errorMessage, $retryAfter, $context);
            case 402:
            case 403:
                if (stripos($errorMessage, 'credit') !== false || stripos($errorMessage, 'balance') !== false) {
                    throw new InsufficientCreditsException($errorMessage, $context);
                }
                throw new OpenRouterException($errorMessage, $httpCode, $context);
            default:
                throw new OpenRouterException($errorMessage, $httpCode, $context);
        }
    }

    /**
     * Parse headers array from cURL format
     *
     * @param array<string> $headers Headers in cURL format
     * @return array<string, string> Associative array
     */
    private function parseHeadersArray(array $headers): array
    {
        $parsed = [];
        foreach ($headers as $header) {
            if (str_contains($header, ':')) {
                [$key, $value] = explode(':', $header, 2);
                $parsed[trim($key)] = trim($value);
            }
        }
        return $parsed;
    }
}

// ============================================
// SECTION 6: FEATURE CLASSES
// ============================================

/**
 * Completions feature - Chat, Text, and Beta Responses
 */
final class Completions
{
    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient
    ) {}

    /**
     * Create a chat completion
     *
     * @param string $model Model ID
     * @param array<array<string, mixed>> $messages Messages array
     * @param ?float $temperature Sampling temperature (0-2)
     * @param ?int $maxTokens Maximum tokens to generate
     * @param ?float $topP Nucleus sampling
     * @param ?bool $stream Stream response
     * @param ?array<string> $stop Stop sequences
     * @param ?array<array<string, mixed>> $tools Tools for function calling
     * @param ?string $toolChoice Tool choice mode
     * @param ?array<string, mixed> $responseFormat Response format
     * @param ?float $presencePenalty Presence penalty
     * @param ?float $frequencyPenalty Frequency penalty
     * @param ?int $seed Random seed
     * @param ?int $topK Top-K sampling
     * @param ?float $topA Top-A sampling
     * @param ?float $minP Min-P sampling
     * @param ?array<string, mixed> $logitBias Logit bias
     * @param ?bool $logprobs Return log probabilities
     * @param ?int $topLogprobs Number of top logprobs
     * @param ?float $repetitionPenalty Repetition penalty
     * @param ?array<string> $transforms OpenRouter transforms
     * @param ?array<string> $models Fallback models
     * @param ?string $route Routing strategy
     * @param ?array<string, mixed> $provider Provider preferences
     * @return array<string, mixed>
     * @throws ValidationException|OpenRouterException
     */
    public function chat(
        string $model,
        array $messages,
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?float $topP = null,
        ?bool $stream = null,
        ?array $stop = null,
        ?array $tools = null,
        ?string $toolChoice = null,
        ?array $responseFormat = null,
        ?float $presencePenalty = null,
        ?float $frequencyPenalty = null,
        ?int $seed = null,
        ?int $topK = null,
        ?float $topA = null,
        ?float $minP = null,
        ?array $logitBias = null,
        ?bool $logprobs = null,
        ?int $topLogprobs = null,
        ?float $repetitionPenalty = null,
        ?array $transforms = null,
        ?array $models = null,
        ?string $route = null,
        ?array $provider = null
    ): array {
        // Validate required parameters
        Validator::auth($this->apiKey);
        Validator::model($model);
        Validator::messages($messages);

        // Validate optional parameters
        if ($temperature !== null) {
            Validator::range($temperature, 0, 2, 'temperature');
        }

        if ($maxTokens !== null) {
            Validator::positiveInteger($maxTokens, 'maxTokens');
        }

        if ($topP !== null) {
            Validator::range($topP, 0, 1, 'topP');
        }

        if ($tools !== null) {
            Validator::tools($tools);
        }

        // Build request body
        $requestBody = [
            'model' => $model,
            'messages' => $messages,
        ];

        // Add optional parameters
        $optionalParams = [
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'top_p' => $topP,
            'stream' => $stream,
            'stop' => $stop,
            'tools' => $tools,
            'tool_choice' => $toolChoice,
            'response_format' => $responseFormat,
            'presence_penalty' => $presencePenalty,
            'frequency_penalty' => $frequencyPenalty,
            'seed' => $seed,
            'top_k' => $topK,
            'top_a' => $topA,
            'min_p' => $minP,
            'logit_bias' => $logitBias,
            'logprobs' => $logprobs,
            'top_logprobs' => $topLogprobs,
            'repetition_penalty' => $repetitionPenalty,
            'transforms' => $transforms,
            'models' => $models,
            'route' => $route,
            'provider' => $provider,
        ];

        foreach ($optionalParams as $key => $value) {
            if ($value !== null) {
                $requestBody[$key] = $value;
            }
        }

        return $this->httpClient->post('/api/v1/chat/completions', $requestBody, $this->apiKey);
    }

    /**
     * Create a text completion (legacy)
     *
     * @param string $model Model ID
     * @param string|array<string> $prompt Prompt text
     * @param ?float $temperature Sampling temperature
     * @param ?int $maxTokens Maximum tokens
     * @param ?float $topP Nucleus sampling
     * @param ?bool $stream Stream response
     * @param ?array<string> $stop Stop sequences
     * @param ?float $presencePenalty Presence penalty
     * @param ?float $frequencyPenalty Frequency penalty
     * @param ?int $bestOf Best of N generations
     * @param ?int $n Number of completions
     * @param ?string $suffix Suffix for completion
     * @param ?bool $echo Echo prompt in response
     * @return array<string, mixed>
     * @throws ValidationException|OpenRouterException
     */
    public function text(
        string $model,
        string|array $prompt,
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?float $topP = null,
        ?bool $stream = null,
        ?array $stop = null,
        ?float $presencePenalty = null,
        ?float $frequencyPenalty = null,
        ?int $bestOf = null,
        ?int $n = null,
        ?string $suffix = null,
        ?bool $echo = null
    ): array {
        // Validate required parameters
        Validator::auth($this->apiKey);
        Validator::model($model);
        Validator::prompt($prompt);

        // Validate optional parameters
        if ($temperature !== null) {
            Validator::range($temperature, 0, 2, 'temperature');
        }

        if ($maxTokens !== null) {
            Validator::positiveInteger($maxTokens, 'maxTokens');
        }

        if ($topP !== null) {
            Validator::range($topP, 0, 1, 'topP');
        }

        // Build request body
        $requestBody = [
            'model' => $model,
            'prompt' => $prompt,
        ];

        // Add optional parameters
        $optionalParams = [
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'top_p' => $topP,
            'stream' => $stream,
            'stop' => $stop,
            'presence_penalty' => $presencePenalty,
            'frequency_penalty' => $frequencyPenalty,
            'best_of' => $bestOf,
            'n' => $n,
            'suffix' => $suffix,
            'echo' => $echo,
        ];

        foreach ($optionalParams as $key => $value) {
            if ($value !== null) {
                $requestBody[$key] = $value;
            }
        }

        return $this->httpClient->post('/api/v1/completions', $requestBody, $this->apiKey);
    }

    /**
     * Create a beta response with reasoning/tools/web search
     *
     * @param array<array<string, mixed>> $messages Messages array
     * @param ?string $reasoningEffort Reasoning effort (low, medium, high)
     * @param ?array<array<string, mixed>> $tools Tools for function calling
     * @param ?bool $webSearch Enable web search
     * @param ?string $model Optional model override
     * @param ?float $temperature Temperature
     * @param ?int $maxTokens Max tokens
     * @param ?float $topP Top P
     * @param ?bool $stream Stream response
     * @param ?array<string> $stop Stop sequences
     * @param ?string $toolChoice Tool choice
     * @param ?array<string, mixed> $responseFormat Response format
     * @return array<string, mixed>
     * @throws ValidationException|OpenRouterException
     */
    public function betaResponses(
        array $messages,
        ?string $reasoningEffort = null,
        ?array $tools = null,
        ?bool $webSearch = null,
        ?string $model = null,
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?float $topP = null,
        ?bool $stream = null,
        ?array $stop = null,
        ?string $toolChoice = null,
        ?array $responseFormat = null
    ): array {
        // Validate required parameters
        Validator::auth($this->apiKey);
        Validator::messages($messages);

        // Validate optional parameters
        if ($reasoningEffort !== null) {
            Validator::enum($reasoningEffort, ['low', 'medium', 'high'], 'reasoningEffort');
        }

        if ($tools !== null) {
            Validator::tools($tools);
        }

        // Build request body
        $requestBody = ['messages' => $messages];

        // Add optional parameters
        $optionalParams = [
            'model' => $model,
            'reasoning_effort' => $reasoningEffort,
            'tools' => $tools,
            'web_search' => $webSearch,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'top_p' => $topP,
            'stream' => $stream,
            'stop' => $stop,
            'tool_choice' => $toolChoice,
            'response_format' => $responseFormat,
        ];

        foreach ($optionalParams as $key => $value) {
            if ($value !== null) {
                $requestBody[$key] = $value;
            }
        }

        return $this->httpClient->post('/api/v1/beta/responses', $requestBody, $this->apiKey);
    }
}

/**
 * Models feature - List and manage models
 */
final class Models
{
    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient
    ) {}

    /**
     * List all available models
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        Validator::auth($this->apiKey);
        return $this->httpClient->get('/api/v1/models', $this->apiKey);
    }

    /**
     * List user-filtered models
     *
     * @return array<string, mixed>
     */
    public function listUser(): array
    {
        Validator::auth($this->apiKey);
        return $this->httpClient->get('/api/v1/models/user', $this->apiKey);
    }

    /**
     * Get total model count
     *
     * @return array<string, mixed>
     */
    public function count(): array
    {
        Validator::auth($this->apiKey);
        return $this->httpClient->get('/api/v1/models/count', $this->apiKey);
    }

    /**
     * Get model endpoints
     *
     * @param string $author Model author
     * @param string $slug Model slug
     * @return array<string, mixed>
     */
    public function endpoints(string $author, string $slug): array
    {
        Validator::auth($this->apiKey);
        Validator::string($author, 'author', 1);
        Validator::string($slug, 'slug', 1);

        $endpoint = sprintf('/api/v1/models/%s/%s/endpoints', urlencode($author), urlencode($slug));
        return $this->httpClient->get($endpoint, $this->apiKey);
    }

    /**
     * Get model endpoints with Zero Data Retention
     *
     * @param string $author Model author
     * @param string $slug Model slug
     * @return array<string, mixed>
     */
    public function endpointsZdr(string $author, string $slug): array
    {
        Validator::auth($this->apiKey);
        Validator::string($author, 'author', 1);
        Validator::string($slug, 'slug', 1);

        $endpoint = sprintf('/api/v1/models/%s/%s/endpoints/zdr', urlencode($author), urlencode($slug));
        return $this->httpClient->get($endpoint, $this->apiKey);
    }

    /**
     * Get model parameters and usage statistics
     *
     * @param string $author Model author
     * @param string $slug Model slug
     * @return array<string, mixed>
     */
    public function parameters(string $author, string $slug): array
    {
        Validator::auth($this->apiKey);
        Validator::string($author, 'author', 1);
        Validator::string($slug, 'slug', 1);

        $endpoint = sprintf('/api/v1/models/%s/%s/parameters', urlencode($author), urlencode($slug));
        return $this->httpClient->get($endpoint, $this->apiKey);
    }

    /**
     * List all providers
     *
     * @return array<string, mixed>
     */
    public function providers(): array
    {
        Validator::auth($this->apiKey);
        return $this->httpClient->get('/api/v1/providers', $this->apiKey);
    }
}

/**
 * Credits feature - Manage credits and payments
 */
final class Credits
{
    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient
    ) {}

    /**
     * Get credit balance and usage
     *
     * @return array<string, mixed>
     */
    public function get(): array
    {
        Validator::auth($this->apiKey);
        return $this->httpClient->get('/api/v1/credits', $this->apiKey);
    }

    /**
     * Create Coinbase charge for crypto payment
     *
     * @param float $amount USD amount
     * @param string $sender Wallet address
     * @param string $chainId Chain ID (ethereum, polygon, base)
     * @return array<string, mixed>
     */
    public function createCoinbaseCharge(float $amount, string $sender, string $chainId): array
    {
        Validator::auth($this->apiKey);

        if ($amount <= 0) {
            throw new ValidationException('Amount must be a positive number');
        }

        Validator::enum($chainId, ['ethereum', 'polygon', 'base'], 'chainId');

        $requestBody = [
            'amount' => $amount,
            'sender' => $sender,
            'chain_id' => $chainId,
        ];

        return $this->httpClient->post('/api/v1/credits/coinbase', $requestBody, $this->apiKey);
    }
}

/**
 * Keys feature - API key management (requires provisioning key)
 */
final class Keys
{
    public function __construct(
        private readonly string $provisioningKey,
        private readonly HttpClient $httpClient
    ) {}

    /**
     * List all API keys
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        Validator::auth($this->provisioningKey);
        return $this->httpClient->get('/api/v1/keys', $this->provisioningKey);
    }

    /**
     * Create a new API key
     *
     * @param string $name Key name
     * @param ?float $limit Credit limit
     * @param ?array<string, mixed> $rateLimit Rate limit configuration
     * @param ?array<string> $allowedModels Allowed models
     * @param ?array<string> $allowedIps Allowed IP addresses
     * @return array<string, mixed>
     */
    public function create(
        string $name,
        ?float $limit = null,
        ?array $rateLimit = null,
        ?array $allowedModels = null,
        ?array $allowedIps = null
    ): array {
        Validator::auth($this->provisioningKey);
        Validator::string($name, 'name', 1);

        $requestBody = ['name' => $name];

        if ($limit !== null) {
            $requestBody['limit'] = $limit;
        }
        if ($rateLimit !== null) {
            $requestBody['rate_limit'] = $rateLimit;
        }
        if ($allowedModels !== null) {
            $requestBody['allowed_models'] = $allowedModels;
        }
        if ($allowedIps !== null) {
            $requestBody['allowed_ips'] = $allowedIps;
        }

        return $this->httpClient->post('/api/v1/keys', $requestBody, $this->provisioningKey);
    }

    /**
     * Get a specific API key
     *
     * @param string $keyId Key ID
     * @return array<string, mixed>
     */
    public function get(string $keyId): array
    {
        Validator::auth($this->provisioningKey);
        Validator::string($keyId, 'keyId', 1);

        return $this->httpClient->get('/api/v1/keys/' . urlencode($keyId), $this->provisioningKey);
    }

    /**
     * Update an API key
     *
     * @param string $keyId Key ID
     * @param ?string $name New name
     * @param ?float $limit New credit limit
     * @param ?array<string, mixed> $rateLimit New rate limit
     * @param ?array<string> $allowedModels New allowed models
     * @param ?array<string> $allowedIps New allowed IPs
     * @return array<string, mixed>
     */
    public function update(
        string $keyId,
        ?string $name = null,
        ?float $limit = null,
        ?array $rateLimit = null,
        ?array $allowedModels = null,
        ?array $allowedIps = null
    ): array {
        Validator::auth($this->provisioningKey);
        Validator::string($keyId, 'keyId', 1);

        $requestBody = [];

        if ($name !== null) {
            $requestBody['name'] = $name;
        }
        if ($limit !== null) {
            $requestBody['limit'] = $limit;
        }
        if ($rateLimit !== null) {
            $requestBody['rate_limit'] = $rateLimit;
        }
        if ($allowedModels !== null) {
            $requestBody['allowed_models'] = $allowedModels;
        }
        if ($allowedIps !== null) {
            $requestBody['allowed_ips'] = $allowedIps;
        }

        if (empty($requestBody)) {
            throw new ValidationException('At least one field to update is required');
        }

        return $this->httpClient->patch('/api/v1/keys/' . urlencode($keyId), $requestBody, $this->provisioningKey);
    }

    /**
     * Delete an API key
     *
     * @param string $keyId Key ID
     * @return array<string, mixed>
     */
    public function delete(string $keyId): array
    {
        Validator::auth($this->provisioningKey);
        Validator::string($keyId, 'keyId', 1);

        return $this->httpClient->delete('/api/v1/keys/' . urlencode($keyId), $this->provisioningKey);
    }

    /**
     * Get current key information (uses regular API key)
     *
     * @param string $apiKey Regular API key
     * @return array<string, mixed>
     */
    public function current(string $apiKey): array
    {
        Validator::auth($apiKey);
        return $this->httpClient->get('/api/v1/key', $apiKey);
    }
}

/**
 * OAuth feature - PKCE flow for API key generation
 */
final class OAuth
{
    public function __construct(
        private readonly HttpClient $httpClient
    ) {}

    /**
     * Generate PKCE verifier
     *
     * @param int $length Length of verifier (43-128)
     * @return string
     */
    public function generatePkceVerifier(int $length = 64): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';
        $verifier = '';

        for ($i = 0; $i < $length; $i++) {
            $verifier .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $verifier;
    }

    /**
     * Generate PKCE challenge from verifier
     *
     * @param string $verifier PKCE verifier
     * @return string
     */
    public function generatePkceChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /**
     * Create OAuth authorization (Step 1)
     *
     * @param string $codeChallenge PKCE code challenge
     * @param string $codeChallengeMethod Challenge method (S256)
     * @param string $redirectUri Redirect URI
     * @param ?string $scope OAuth scope
     * @param ?string $state State parameter
     * @return array<string, mixed>
     */
    public function createAuthorization(
        string $codeChallenge,
        string $codeChallengeMethod,
        string $redirectUri,
        ?string $scope = null,
        ?string $state = null
    ): array {
        Validator::enum($codeChallengeMethod, ['S256'], 'codeChallengeMethod');

        $requestBody = [
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => $codeChallengeMethod,
            'redirect_uri' => $redirectUri,
        ];

        if ($scope !== null) {
            $requestBody['scope'] = $scope;
        }
        if ($state !== null) {
            $requestBody['state'] = $state;
        }

        return $this->httpClient->post('/api/v1/auth/keys', $requestBody, '');
    }

    /**
     * Exchange authorization code for API key (Step 2)
     *
     * @param string $code Authorization code
     * @param string $codeVerifier PKCE code verifier
     * @return array<string, mixed>
     */
    public function exchangeCode(string $code, string $codeVerifier): array
    {
        $queryParams = [
            'code' => $code,
            'code_verifier' => $codeVerifier,
        ];

        return $this->httpClient->get('/api/v1/auth/keys?' . http_build_query($queryParams), '');
    }
}

/**
 * Generations feature - Track generation metadata
 */
final class Generations
{
    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient
    ) {}

    /**
     * Get generation metadata and usage stats
     *
     * @param string $generationId Generation ID
     * @return array<string, mixed>
     */
    public function get(string $generationId): array
    {
        Validator::auth($this->apiKey);
        Validator::string($generationId, 'generationId', 1);

        return $this->httpClient->get('/api/v1/generation?id=' . urlencode($generationId), $this->apiKey);
    }
}

/**
 * Analytics feature - Get usage activity data
 */
final class Analytics
{
    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient
    ) {}

    /**
     * Get activity analytics
     *
     * @param ?string $startDate Start date (YYYY-MM-DD)
     * @param ?string $endDate End date (YYYY-MM-DD)
     * @return array<string, mixed>
     */
    public function activity(?string $startDate = null, ?string $endDate = null): array
    {
        Validator::auth($this->apiKey);

        $queryParams = [];
        if ($startDate !== null) {
            $queryParams['start_date'] = $startDate;
        }
        if ($endDate !== null) {
            $queryParams['end_date'] = $endDate;
        }

        $endpoint = '/api/v1/activity';
        if (!empty($queryParams)) {
            $endpoint .= '?' . http_build_query($queryParams);
        }

        return $this->httpClient->get($endpoint, $this->apiKey);
    }
}

// ============================================
// SECTION 7: MAIN CLIENT CLASS
// ============================================

/**
 * OpenRouter Client - Main entry point
 *
 * Provides fluent interface to all OpenRouter API features.
 *
 * Example usage:
 * ```php
 * $client = new OpenRouterClient('sk-or-v1-xxx');
 *
 * // Chat completion
 * $response = $client->completions()->chat(
 *     model: 'openai/gpt-4',
 *     messages: [['role' => 'user', 'content' => 'Hello!']]
 * );
 *
 * // List models
 * $models = $client->models()->list();
 *
 * // Get credits
 * $credits = $client->credits()->get();
 * ```
 */
final class OpenRouterClient
{
    private readonly HttpClient $httpClient;
    private readonly ?Logger $logger;
    private readonly string $apiKey;
    private readonly array $config;

    /**
     * Create a new OpenRouter client
     *
     * @param string $apiKey OpenRouter API key
     * @param ?array<string, mixed> $config Optional configuration
     */
    public function __construct(string $apiKey, ?array $config = null)
    {
        $this->apiKey = $apiKey;
        $this->config = $config ?? [];

        // Initialize logger if logging is enabled
        $this->logger = new Logger($this->config);

        // Initialize HTTP client
        $this->httpClient = new HttpClient($this->config, $this->logger);

        // Validate API key
        Validator::auth($apiKey);
    }

    /**
     * Get completions feature (chat, text, beta responses)
     *
     * @return Completions
     */
    public function completions(): Completions
    {
        return new Completions($this->apiKey, $this->httpClient);
    }

    /**
     * Get models feature (list, details, parameters, providers)
     *
     * @return Models
     */
    public function models(): Models
    {
        return new Models($this->apiKey, $this->httpClient);
    }

    /**
     * Get credits feature (balance, coinbase payments)
     *
     * @return Credits
     */
    public function credits(): Credits
    {
        return new Credits($this->apiKey, $this->httpClient);
    }

    /**
     * Get keys feature (API key management)
     *
     * Requires a provisioning API key (different from regular API key).
     *
     * @param string $provisioningKey Provisioning API key
     * @return Keys
     */
    public function keys(string $provisioningKey): Keys
    {
        return new Keys($provisioningKey, $this->httpClient);
    }

    /**
     * Get OAuth feature (PKCE flow)
     *
     * @return OAuth
     */
    public function oauth(): OAuth
    {
        return new OAuth($this->httpClient);
    }

    /**
     * Get generations feature (track generation metadata)
     *
     * @return Generations
     */
    public function generations(): Generations
    {
        return new Generations($this->apiKey, $this->httpClient);
    }

    /**
     * Get analytics feature (usage activity data)
     *
     * @return Analytics
     */
    public function analytics(): Analytics
    {
        return new Analytics($this->apiKey, $this->httpClient);
    }

    /**
     * Get the logger instance
     *
     * @return ?Logger
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }

    /**
     * Get the HTTP client instance
     *
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Get current configuration
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

// ============================================
// END OF FILE
// ============================================

