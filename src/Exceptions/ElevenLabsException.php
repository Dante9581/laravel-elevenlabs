<?php

namespace DigitalCoreHub\LaravelElevenLabs\Exceptions;

use Exception;

class ElevenLabsException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create an exception from an API error response.
     */
    public static function fromApiResponse(array $response, int $statusCode = 500): self
    {
        $message = $response['detail']['message'] ?? $response['detail'] ?? 'An error occurred with the ElevenLabs API';

        return new self($message, $statusCode);
    }
}
