<?php namespace Hostbase\Http;

use Hostbase\Entity\EntityTransformer;
use Hostbase\Entity\Exceptions\EntityNotFound;
use Hostbase\Exceptions\NoSearchResults;
use Hostbase\ListTransformer;
use Hostbase\Services\BaseResourceService;
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
     * @var BaseResourceService
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



    const CODE_WRONG_ARGS = 'Incorrect arguments';
    const CODE_NOT_FOUND = 'Resource not found';
    const CODE_INTERNAL_ERROR = 'Internal error';
    const CODE_UNAUTHORIZED = 'Unauthorized';
    const CODE_FORBIDDEN = 'Forbidden';


    /**
     * @param BaseResourceService $service
     * @param Manager $fractal
     * @param EntityTransformer $transformer
     */
    public function __construct(BaseResourceService $service, Manager $fractal, EntityTransformer $transformer)
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
        // handle search
        if (Input::has('q')) {

            $showData = Input::has('showData') ? (bool) Input::get('showData') : false;

            if ($showData === true) {
                $this->setTransformerFilters();
            }

            try {
                $resources = $this->service->search(
                    Input::get('q'),
                    Input::has('size') ? Input::get('size') : 10000,
                    $showData
                );
                return $this->respondWithCollection($resources, $showData === true ? $this->transformer : new ListTransformer());
            } catch (NoSearchResults $e) {
                return $this->errorNotFound($e->getMessage());
            } catch (\Exception $e) {
                return $this->errorInternalError($e->getMessage());
            }
        } else {
            return $this->respondWithCollection($this->service->showList(), new ListTransformer());
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
            $entity = $this->service->makeNewEntity(null, $data);
            $resource = $this->service->store($entity);

            return $this->setStatusCode(HttpResponse::HTTP_CREATED)->respondWithItem($resource, $this->transformer);
        } catch (\Exception $e) {
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
            return $this->respondWithItem($this->service->showOne($id), $this->transformer);
        } catch (EntityNotFound $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
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
            $updatedResource = $this->service->update($id, $data);

            return $this->respondWithItem($updatedResource, $this->transformer);
        } catch (EntityNotFound $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
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
            $this->service->destroy($id);

            return Response::json("Deleted $id");
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
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