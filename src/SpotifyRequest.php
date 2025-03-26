<?php

namespace Aerni\Spotify;

use Aerni\Spotify\Exceptions\SpotifyApiException;
use Aerni\Spotify\Facades\SpotifyClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SpotifyRequest
{
    private $accessToken;

    private $apiUrl;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->apiUrl = config('spotify.api_url', 'https://api.spotify.com/v1');
    }

    /**
     * Make the API request.
     *
     * @throws SpotifyApiException
     */
    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->apiUrl.$endpoint.'?'.http_build_query($params);
        $startTime = microtime(true);

        $this->logRequest('GET', $url, $params);

        try {
            $response = SpotifyClient::get($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accepts' => 'application/json',
                    'Authorization' => 'Bearer '.$this->accessToken,
                ],
            ]);

            $responseData = json_decode((string) $response->getBody(), true);
            $duration = microtime(true) - $startTime;

            $this->logResponse('GET', $url, $response->getStatusCode(), $responseData, $duration);

            return $responseData;
        } catch (RequestException $e) {
            $errorResponse = $e->getResponse();
            $status = $errorResponse->getStatusCode();
            $message = $errorResponse->getReasonPhrase();
            $duration = microtime(true) - $startTime;

            $this->logError('GET', $url, $status, $message, $duration);

            throw new SpotifyApiException($message, $status, $errorResponse);
        }
    }

    /**
     * Log the API request.
     */
    private function logRequest(string $method, string $url, array $params = []): void
    {
        if (!config('spotify.logging.enabled', false)) {
            return;
        }

        $logData = [
            'type' => 'spotify_request',
            'method' => $method,
            'url' => $url,
        ];

        if (config('spotify.logging.log_request_body', true) && !empty($params)) {
            $logData['params'] = $params;
        }

        $this->log('info', 'Spotify API Request', $logData);
    }

    /**
     * Log the API response.
     */
    private function logResponse(string $method, string $url, int $statusCode, array $responseData, float $duration): void
    {
        if (!config('spotify.logging.enabled', false)) {
            return;
        }

        $logData = [
            'type' => 'spotify_response',
            'method' => $method,
            'url' => $url,
            'status_code' => $statusCode,
            'duration_ms' => round($duration * 1000, 2),
        ];

        if (config('spotify.logging.log_response_body', true)) {
            $logData['response'] = $responseData;
        }

        $this->log('info', 'Spotify API Response', $logData);
    }

    /**
     * Log API errors.
     */
    private function logError(string $method, string $url, int $statusCode, string $message, float $duration): void
    {
        if (!config('spotify.logging.enabled', false)) {
            return;
        }

        $logData = [
            'type' => 'spotify_error',
            'method' => $method,
            'url' => $url,
            'status_code' => $statusCode,
            'error_message' => $message,
            'duration_ms' => round($duration * 1000, 2),
        ];

        $this->log('error', 'Spotify API Error', $logData);
    }

    /**
     * Write log entry.
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $channel = config('spotify.logging.channel', 'single');
        $configLevel = config('spotify.logging.level', 'info');

        // Verifica che il livello di log configurato permetta questo log
        $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];
        $configLevelIndex = array_search($configLevel, $levels);
        $currentLevelIndex = array_search($level, $levels);

        if ($currentLevelIndex >= $configLevelIndex) {
            Log::channel($channel)->$level($message, $context);
        }
    }
}
