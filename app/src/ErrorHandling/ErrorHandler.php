<?php namespace Hostbase\ErrorHandling;

use Exception;
use Illuminate\Http\Response as HttpResponse;
use Log;
use Response;


/**
 * Class ErrorHandler
 *
 * @package Hostbase\ErrorHandling
 */
class ErrorHandler
{
    const CODE_WRONG_ARGS = 'Incorrect arguments';
    const CODE_NOT_FOUND = 'Resource not found';
    const CODE_UNSUPPORTED_MEDIA_TYPE = 'Content type not supported';
    const CODE_INTERNAL_ERROR = 'Internal error';
    const CODE_UNAUTHORIZED = 'Unauthorized';
    const CODE_FORBIDDEN = 'Forbidden';
    const CODE_CONFLICT = 'Conflict';


    /**
     * @param Exception $exception
     */
    public static function logException(Exception $exception)
    {
        Log::error($exception);
    }


    /**
     * @param string $message
     * @param $errorCode
     * @param int $statusCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected static function respondWithError($message, $errorCode, $statusCode)
    {
        return Response::json(
            [
                'error' => [
                    'code'      => $errorCode,
                    'http_code' => $statusCode,
                    'message'   => $message,
                ]
            ],
            $statusCode
        );
    }


    /*
     * Error responses
     */

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public static function errorForbidden($message = 'Forbidden')
    {
        return self::respondWithError($message, self::CODE_FORBIDDEN, HttpResponse::HTTP_FORBIDDEN);
    }


    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @param string $message
     * @param string|null   $code
     *
     * @return Response
     */
    public static function errorInternalError($message = 'Internal Error', $code = null)
    {
        return self::respondWithError($message, $code ?: self::CODE_INTERNAL_ERROR, HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public static function errorNotFound($message = 'Resource Not Found')
    {
        return self::respondWithError($message, self::CODE_NOT_FOUND, HttpResponse::HTTP_NOT_FOUND);
    }


    /**
     * Generates a Response with a 406 HTTP header and a message indicating that
     * a Content-Type header value of 'application/json' is required.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function errorMustBeJson()
    {
        return self::errorUnsupportedMediaType('Content-Type must be application/json');
    }


    /**
     * Generates a Response with a 406 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function errorUnsupportedMediaType($message = 'Content type not supported')
    {
        return self::respondWithError($message, self::CODE_UNSUPPORTED_MEDIA_TYPE, HttpResponse::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }


    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public static function errorUnauthorized($message = 'Unauthorized')
    {
        return self::respondWithError($message, self::CODE_UNAUTHORIZED, HttpResponse::HTTP_UNAUTHORIZED);
    }


    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public static function errorWrongArgs($message = 'Wrong Arguments')
    {
        return self::respondWithError($message, self::CODE_WRONG_ARGS, HttpResponse::HTTP_BAD_REQUEST);
    }


    /**
     * Generates a Repsonse with a 409 HTTP header and a given message.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function errorConflict($message = 'Conflict')
    {
        return self::respondWithError($message, self::CODE_CONFLICT, HttpResponse::HTTP_CONFLICT);
    }
} 