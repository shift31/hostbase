<?php namespace Hostbase\Http;

use Hostbase\Entity\EntityTransformer;
use Hostbase\ErrorHandling\ErrorHandler;
use Hostbase\ListTransformer;
use Hostbase\Services\DefaultResourceService;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use Input;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Response;
use Symfony\Component\Yaml\Yaml;


/**
 * Class ResourceController
 *
 * Abstract class for handling API routes
 */
abstract class ResourceController extends Controller
{
    use ControllerHelpers;


    /**
     * @var DefaultResourceService
     */
    protected $service;

    /**
     * @var \League\Fractal\Manager
     */
    protected $fractal;

    /**
     * @var EntityTransformer
     */
    protected $transformer;


    /**
     * @param DefaultResourceService $service
     * @param Manager                $fractal
     * @param EntityTransformer      $transformer
     */
    public function __construct(DefaultResourceService $service, Manager $fractal, EntityTransformer $transformer)
    {
        $this->service = $service;
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
        $showData = Input::has('showData') ? (bool) Input::get('showData') : false;

        if ($showData === true) {
            $this->setTransformerFilters();
        }

        $limit = Input::has('limit') ? Input::get('limit') : 10000;


        // handle search
        if (Input::has('q')) {

            $resources = $this->service->search(
                Input::get('q'),
                $limit,
                $showData
            );

            return $this->respondWithCollection($resources,
                $showData === true ? $this->transformer : new ListTransformer());
        } else {
            return $this->respondWithCollection($this->service->showList($limit, $showData),
                $showData === true ? $this->transformer : new ListTransformer());
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store()
    {
        if (Input::isJson()) {

            $data = Input::all();

        } elseif ($this->requestContainsYaml()) {

            $data = Yaml::parse(Input::getContent());

        } else {
            return ErrorHandler::errorUnsupportedMediaType("Content-Type must be 'application/json' or 'application/yaml");
        }

        $resource = $this->service->store($data);

        return $this->setStatusCode(HttpResponse::HTTP_CREATED)->respondWithItem($resource, $this->transformer);
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

        return $this->respondWithItem($this->service->showOne($id), $this->transformer);
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
            return ErrorHandler::errorUnsupportedMediaType("Content-Type must be 'application/json' or 'application/yaml");
        }

        $updatedResource = $this->service->update($id, $data);

        return $this->respondWithItem($updatedResource, $this->transformer);
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
        $this->service->destroy($id);

        return Response::json("Deleted $id", HttpResponse::HTTP_ACCEPTED);
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


    protected function setTransformerFilters()
    {
        if (Input::has('include')) {
            $this->transformer->setFieldIncludes(explode(',', Input::get('include')));
        } elseif (Input::has('exclude')) {
            $this->transformer->setFieldExcludes(explode(',', Input::get('exclude')));
        }
    }
}