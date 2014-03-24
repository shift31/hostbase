<?php

use Hostbase\Entity\EntityTransformer;
use Mockery as m;

class EntityTransformerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var array
     */
    protected $dummyData = ['one' => 1, 'two' => 2, 'three' => 3];

    /**
     * @var m\Mock;
     */
    protected $entity;


    public function setUp()
    {
        $mock = m::mock('Hostbase\Entity\BaseEntity');
        $mock->shouldReceive('setData')->with($this->dummyData);
        $this->entity = $mock;
    }


    /** @test */
    public function it_returns_all_data_if_there_are_no_includes_or_excludes()
    {
        $resourceTransformer = new EntityTransformer();

        $this->entity->shouldReceive('getData')->once()->andReturn($this->dummyData);

        $transformedResource = $resourceTransformer->transform($this->entity);

        $this->assertEquals($this->dummyData, $transformedResource);
    }


    /** @test */
    public function it_can_filter_resource_data_using_includes()
    {
        $resourceTransformer = new EntityTransformer();

        $resourceTransformer->setIncludes(['two']);

        $transformedDummy = ['two' => 2];

        $this->entity->shouldReceive('getData')->once()->andReturn($this->dummyData);
        $this->entity->shouldReceive('setData')->once()->with($transformedDummy);
        $this->entity->shouldReceive('getData')->once()->andReturn($transformedDummy);

        $transformedResource = $resourceTransformer->transform($this->entity);

        $this->assertArrayNotHasKey('one', $transformedResource);

        $this->assertArrayNotHasKey('three', $transformedResource);
    }


    /** @test */
    public function it_can_filter_resource_data_using_excludes()
    {
        $resourceTransformer = new EntityTransformer();

        $resourceTransformer->setExcludes(['two']);

        $transformedDummy = ['one' => 1, 'three' => 3];

        $this->entity->shouldReceive('getData')->once()->andReturn($this->dummyData);
        $this->entity->shouldReceive('setData')->once()->with($transformedDummy);
        $this->entity->shouldReceive('getData')->once()->andReturn($transformedDummy);

        $transformedResource = $resourceTransformer->transform($this->entity);

        $this->assertArrayNotHasKey('two', $transformedResource);
    }
} 