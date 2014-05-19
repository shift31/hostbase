<?php namespace Hostbase\Entity;

use League\Fractal\TransformerAbstract;


class EntityTransformer extends TransformerAbstract
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
     * @param Entity $entity
     * @return array
     */
    public function transform(Entity $entity)
    {
        if (count($this->includes) > 0) {
            $this->filterResourceForIncludes($entity);
        }

        if (count($this->excludes) > 0) {
            $this->filterResourceForExcludes($entity);
        }

        $data = $entity->getData();

        // remove docType and _timestamp keys, as they're only used internally
        unset($data['docType']);
        unset($data['_timestamp']);

        return $data;
    }


    /**
     * @param Entity $entity
     */
    protected function filterResourceForIncludes(Entity $entity)
    {
        $data = $entity->getData();

        $filteredData = [];

        foreach ($this->includes as $include) {
            $filteredData[$include] = $data[$include];
        }

        $entity->setData($filteredData);
    }


    /**
     * @param Entity $entity
     */
    protected function filterResourceForExcludes(Entity $entity)
    {
        $data = $entity->getData();

        foreach ($this->excludes as $exclude) {
            unset($data[$exclude]);
        }

        $entity->setData($data);
    }
} 