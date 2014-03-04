<?php

use Hostbase\Exceptions\NoSearchResultsException;
use Hostbase\Exceptions\ResourceNotFoundException;
use Hostbase\PassThruResourceTransformer;
use Hostbase\ResourceRepository;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\Yaml\Yaml;


abstract class ResourceController extends Controller
{
    /**
     * @var Hostbase\ResourceRepository
     */
    protected $resources;

    /**
     * @var League\Fractal\Manager
     */
    protected $fractal;

    /**
     * @var int
     */
    protected $statusCode = 200;


    const CODE_WRONG_ARGS = 'Incorrect arguments';
    const CODE_NOT_FOUND = 'Resource not found';
    const CODE_INTERNAL_ERROR = 'Internal error';
    const CODE_UNAUTHORIZED = 'Unauthorized';
    const CODE_FORBIDDEN = 'Forbidden';


    /**
     * @param ResourceRepository $resources
     * @param Manager            $fractal
     */
    public function __construct(ResourceRepository $resources, Manager $fractal)
    {
        $this->resources = $resources;
        $this->fractal = $fractal;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // handle search
        if (Input::has('q')) {
            try {
                $resources = $this->resources->search(
                    Input::get('q'),
                    Input::has('size') ? Input::get('size') : 10000,
                    Input::has('showData') ? (bool) Input::get('showData') : true
                );

                return $this->respondWithCollection($resources, new PassThruResourceTransformer());
            } catch (NoSearchResultsException $e) {
                return $this->errorNotFound($e->getMessage());
            } catch (Exception $e) {
                return $this->errorInternalError($e->getMessage());
            }
        } else {
            return $this->respondWithCollection($this->resources->show(), new PassThruResourceTransformer());
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        if (Input::isJson()) {

            $data = Input::all();

        } elseif ($this->requestContainsYaml()) {

            $data = Yaml::parse(Input::getContent());

        } else {
            return $this->errorInternalError("Content-Type must be 'application/json' or 'application/yaml");
        }

        try {
            $resource = $this->resources->store($data);

            return $this->setStatusCode(201)->respondWithItem($resource, new PassThruResourceTransformer());
        } catch (Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            return $this->respondWithItem($this->resources->show($id), new PassThruResourceTransformer());
        } catch (ResourceNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        if (Input::isJson()) {

            $data = Input::all();

        } elseif ($this->requestContainsYaml()) {

            $data = Yaml::parse(Input::getContent());

        } else {
            return $this->errorInternalError("Content-Type must be 'application/json' or 'application/yaml");
        }

        try {
            $updatedData = $this->resources->update($id, $data);

            return $this->respondWithItem($updatedData, new PassThruResourceTransformer());
        } catch (ResourceNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $this->resources->destroy($id);

            return Response::json("Deleted $id");
        } catch (Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }

    }


    /*
     * Request & Response Handling
     */

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
     * @param $item
     * @param $callback
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithItem($item, $callback)
    {
        $resource = new Item($item, $callback);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }


    /**
     * @param $collection
     * @param $callback
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithCollection($collection, $callback)
    {
        $resource = new Collection($collection, $callback);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
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


    /**
     * @param $message
     * @param $errorCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message, $errorCode)
    {
        if ($this->statusCode === 200) {
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
        return $this->setStatusCode(403)->respondWithError($message, self::CODE_FORBIDDEN);
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
        return $this->setStatusCode(500)->respondWithError($message, self::CODE_INTERNAL_ERROR);
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
        return $this->setStatusCode(404)->respondWithError($message, self::CODE_NOT_FOUND);
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
        return $this->setStatusCode(401)->respondWithError($message, self::CODE_UNAUTHORIZED);
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
        return $this->setStatusCode(400)->respondWithError($message, self::CODE_WRONG_ARGS);
    }
}