<?php namespace Hostbase\Http;

use Request;
use Symfony\Component\Yaml\Yaml;
use Response;
use Illuminate\Http\Response as HttpResponse;


/**
 * Class ControllerHelpers
 * @package Hostbase\Http
 */
trait ControllerHelpers
{
    /**
     * @var int
     */
    protected $statusCode = HttpResponse::HTTP_OK;


    /**
     * Getter for statusCode
     *
     * @return mixed
     */
    protected function getStatusCode()
    {
        return $this->statusCode;
    }


    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return ResourceController
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }


    /*
     * Requests
     */

    /**
     * @return bool
     */
    protected function requestAcceptsYaml()
    {
        return Request::header('Accept') == 'application/yaml';
    }


    /**
     * @return bool
     */
    protected function requestContainsYaml()
    {
        return Request::header('Content-Type') == 'application/yaml';
    }


    /*
     * Responses
     */

    /**
     * @param array $array
     * @param array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        if ($this->requestAcceptsYaml()) {
            $response = Response::make(
                Yaml::dump($array),
                $this->statusCode,
                array_merge($headers, ['Content-Type' => 'application/yaml'])
            );
        } else {
            $response = Response::json($array, $this->statusCode, $headers);
        }

        return $response;
    }


    /**
     * @param $message
     * @param $errorCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message, $errorCode)
    {
        if ($this->statusCode === HttpResponse::HTTP_OK) {
            trigger_error(
                "An error response was requested, but the HTTP status code is 200!?",
                E_USER_WARNING
            );
        }

        return $this->respondWithArray(
            [
                'error' => [
                    'code'      => $errorCode,
                    'http_code' => $this->statusCode,
                    'message'   => $message,
                ]
            ]
        );
    }


    /*
     * Error responses
     */

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(HttpResponse::HTTP_FORBIDDEN)->respondWithError($message, self::CODE_FORBIDDEN);
    }


    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(HttpResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message, self::CODE_INTERNAL_ERROR);
    }


    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(HttpResponse::HTTP_NOT_FOUND)->respondWithError($message, self::CODE_NOT_FOUND);
    }


    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(HttpResponse::HTTP_UNAUTHORIZED)->respondWithError($message, self::CODE_UNAUTHORIZED);
    }


    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(HttpResponse::HTTP_BAD_REQUEST)->respondWithError($message, self::CODE_WRONG_ARGS);
    }
}