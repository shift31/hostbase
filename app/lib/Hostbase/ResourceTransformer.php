<?php namespace Hostbase;

use League\Fractal\TransformerAbstract;


class ResourceTransformer extends TransformerAbstract
{
    /**
     * @var array
     */
    protected $includes = [];

    /**
     * @var array
     */
    protected $excludes = [];


    /**
     * @param array $includes
     * @return $this
     */
    public function setIncludes(array $includes)
    {
        $this->includes = $includes;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getIncludes()
    {
        return $this->includes;
    }


    /**
     * @param array $excludes
     * @return $this
     */
    public function setExcludes(array $excludes)
    {
        $this->excludes = $excludes;

        return $this;
    }


    /**
     * @return array
     */
    public function getExcludes()
    {
        return $this->excludes;
    }


    /**
     * @param $resource
     * @return array
     */
    public function transform($resource)
    {
        if (count($this->includes) > 0) {
            return $this->filterResourceForIncludes($resource);
        }

        if (count($this->excludes) > 0) {
            $this->filterResourceForExcludes($resource);
            return $resource;
        }

        return $resource;
    }


    /**
     * @param $resource
     * @return array
     */
    protected function filterResourceForIncludes($resource)
    {
        $filteredResource = [];

        foreach ($this->includes as $include) {
            $filteredResource[$include] = $resource[$include];
        }

        return $filteredResource;
    }


    /**
     * @param $resource
     */
    protected function filterResourceForExcludes(&$resource)
    {
        foreach ($this->excludes as $exclude) {
            unset($resource[$exclude]);
        }
    }
} 