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
}