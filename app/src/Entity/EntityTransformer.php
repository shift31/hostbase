<?php namespace Hostbase\Entity;

use League\Fractal\TransformerAbstract;


class EntityTransformer extends TransformerAbstract
{
    /**
     * @var array
     */
    protected $fieldIncludes = [];

    /**
     * @var array
     */
    protected $fieldExcludes = [];


    /**
     * @param array $includes
     * @return $this
     */
    public function setFieldIncludes(array $includes)
    {
        $this->fieldIncludes = $includes;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getFieldIncludes()
    {
        return $this->fieldIncludes;
    }


    /**
     * @param array $excludes
     * @return $this
     */
    public function setFieldExcludes(array $excludes)
    {
        $this->fieldExcludes = $excludes;

        return $this;
    }


    /**
     * @return array
     */
    public function getFieldExcludes()
    {
        return $this->fieldExcludes;
    }


    /**
     * @param Entity $entity
     * @return array
     */
    public function transform(Entity $entity)
    {
        $data = $entity->toArray();

        if (count($this->fieldIncludes) > 0) {
            $this->filterIncludes($data);
        }

        if (count($this->fieldExcludes) > 0) {
            $this->filterEntityForExcludes($data);
        }

        // remove docType and _timestamp keys, as they're only used internally
        unset($data['docType']);
        unset($data['_timestamp']);

        return $data;
    }


    /**
     * @param $data
     */
    protected function filterIncludes(&$data)
    {
        $filteredData = [];

        foreach ($this->fieldIncludes as $include) {
            $filteredData[$include] = $data[$include];
        }

        $data = $filteredData;
    }


    /**
     * @param $data
     */
    protected function filterEntityForExcludes(&$data)
    {
        foreach ($this->fieldExcludes as $exclude) {
            unset($data[$exclude]);
        }
    }
} 