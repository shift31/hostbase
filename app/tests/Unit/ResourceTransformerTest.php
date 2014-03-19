<?php

use Hostbase\ResourceTransformer;

class ResourceTransformerTest extends PHPUnit_Framework_TestCase {


    /** @test */
    public function it_returns_an_unmodified_resource_if_there_are_no_includes_or_excludes()
    {
        $resourceTransformer = new ResourceTransformer();

        $resource = $this->makeDummyResource();

        $transformedResource = $resourceTransformer->transform($resource);

        $this->assertEquals($resource, $transformedResource);
    }


    /** @test */
    public function it_can_filter_resource_keys_using_includes()
    {
        $resourceTransformer = new ResourceTransformer();

        $resource = $this->makeDummyResource();

        $resourceTransformer->setIncludes(['two']);

        $transformedResource = $resourceTransformer->transform($resource);

        $this->assertArrayNotHasKey('one', $transformedResource);

        $this->assertArrayNotHasKey('three', $transformedResource);
    }


    /** @test */
    public function it_can_filter_resource_keys_using_excludes()
    {
        $resourceTransformer = new ResourceTransformer();

        $resource = $this->makeDummyResource();

        $resourceTransformer->setExcludes(['two']);

        $transformedResource = $resourceTransformer->transform($resource);

        $this->assertArrayNotHasKey('two', $transformedResource);
    }


    protected function makeDummyResource()
    {
        return ['one' => 1, 'two' => 2, 'three' => 3];
    }
} 