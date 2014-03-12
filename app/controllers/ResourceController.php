<?php

use Hostbase\Exceptions\NoSearchResultsException;
use Hostbase\Exceptions\ResourceNotFoundException;
use Hostbase\ResourceTransformer;
use Hostbase\ResourceRepository;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Http\Response as HttpResponse;


/**
 * Class ResourceController
 *
 * Abstract class for handling API routes
 *
 * @todo use transformer to dynamically filter data
 */
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
     * @var League\Fractal\TransformerAbstract
     */
    protected $transformer;

    /**
     * @var int
     */
    protected $statusCode = HttpResponse::HTTP_OK;


    const CODE_WRONG_ARGS = 'Incorrect arguments';
    const CODE_NOT_FOUND = 'Resource not found';
    const CODE_INTERNAL_ERROR = 'Internal error';
    const CODE_UNAUTHORIZED = 'Unauthorized';
    const CODE_FORBIDDEN = 'Forbidden';


    /**
     * @param ResourceRepository $resources
     * @param Manager $fractal
     * @param ResourceTransformer $transformer
     */
    public function __construct(ResourceRepository $resources, Manager $fractal, ResourceTransformer $transformer)
    {
        $this->resources = $resources;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
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

            $showData = Input::has('showData') ? (bool) Input::get('showData') : true;

            if ($showData === true) {
                $this->setTransformerFilters();
            }

            try {
                $resources = $this->resources->search(
                    Input::get('q'),
                    Input::has('size') ? Input::get('size') : 10000,
                    $showData
                );
                return $this->respondWithCollection($resources, $this->transformer);
            } catch (NoSearchResultsException $e) {
                return $this->errorNotFound($e->getMessage());
            } catch (Exception $e) {
                return $this->errorInternalError($e->getMessage());
            }
        } else {
            return $this->respondWithCollection($this->resources->show(), $this->transformer);
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

            return $this->setStatusCode(HttpResponse::HTTP_CREATED)->respondWithItem($resource, $this->transformer);
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
        $this->setTransformerFilters();

        try {
            return $this->respondWithItem($this->resources->show($id), $this->transformer);
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

            return $this->respondWithItem($updatedData, $this->transformer);
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

    protected function setTransformerFilters()
    {
        if (Input::has('include')) {
            $this->transformer->setIncludes(explode(',', Input::get('include')));
        } elseif (Input::has('exclude')) {
            $this->transformer->setExcludes(explode(',', Input::get('exclude')));
        }
    }


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