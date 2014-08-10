<?php

use Hostbase\Host\HostService;


class HostServiceTest extends TestCase {

    const TEST_FQDN = 'test.example.com';
    const TEST_FQDN_2 = 'test2.example.com';

    /**
     * @var HostService
     */
    protected $service;


    public function setUp()
    {
        parent::setUp();

        $cb = App::make('basement');

        $es = App::make('elasticsearch');

        $this->service = new HostService($cb, $es);

        $host = $this->service->makeNewEntity();
        $host->setFqdn(self::TEST_FQDN);

        $this->service->store($host);

        // give elasticsearch a second to index
        sleep(1);
    }


    public function tearDown()
    {
        $this->service->destroy(self::TEST_FQDN);
    }


    /** @test */
    public function it_can_store_a_host()
    {
        $host = $this->service->makeNewEntity();

        $host->setFqdn(self::TEST_FQDN_2);

        $hostFromRepo = $this->service->store($host);

        $data = $hostFromRepo->getData();

        $this->assertArrayHasKey('hostname', $data);
        $this->assertArrayHasKey('domain', $data);
        $this->assertArrayHasKey('docType', $data);
        $this->assertArrayHasKey('createdDateTime', $data);
    }


    /**
     * @test
     * @expectedException Hostbase\Entity\Exceptions\EntityAlreadyExists
     */
    public function it_throws_an_exception_if_host_already_exists()
    {
        $host = $this->service->makeNewEntity();
        $host->setFqdn(self::TEST_FQDN);

        $this->service->store($host);
    }


    /**
     * @test
     * @expectedException Hostbase\Host\HostMissingFqdn
     */
    public function it_throws_an_exception_if_host_is_missing_fqdn()
    {
        $host = $this->service->makeNewEntity();
        $host->setData(['foo' => 'bar']);

        $this->service->store($host);
    }


    /** @test */
    public function it_can_search_for_and_find_an_existing_host()
    {
        $hosts = $this->service->search(self::TEST_FQDN);

        $this->assertContains(self::TEST_FQDN, $hosts);
    }


    /** @test */
    public function it_can_search_for_and_find_an_existing_host_and_show_its_data()
    {
        $hosts = $this->service->search(self::TEST_FQDN, 10000, true);

        $firstHostData = $hosts[0]->getData();

        $this->assertEquals('test', $firstHostData['hostname']);
        $this->assertEquals('example.com', $firstHostData['domain']);
    }


    /**
     * @test
     * @expectedException \Hostbase\Exceptions\NoSearchResults
     */
    public function it_throws_an_exception_if_there_are_no_search_results()
    {
        $this->service->search('NOSUCHHOST.example.com');
    }


    /** @test */
    public function it_can_show_a_host()
    {
        $host = $this->service->showOne(self::TEST_FQDN);
        $data = $host->getData();

        $this->assertEquals(self::TEST_FQDN, $data['fqdn']);
        $this->assertEquals('test', $data['hostname']);
        $this->assertEquals('example.com', $data['domain']);
    }


    /** @test */
    public function it_can_list_all_hosts()
    {
        $list = $this->service->showList();

        $this->assertContains(self::TEST_FQDN, $list);
    }


    /** @test */
    public function it_can_update_a_host()
    {
        $host = $this->service->makeNewEntity();
        $host->setFqdn(self::TEST_FQDN);
        $host->setData(['foo' => 'bar']);

        $updatedHost = $this->service->update($host);

        $this->assertEquals('bar', $updatedHost->getData()['foo']);
    }


    /**
     * @test
     * @expectedException \Hostbase\Entity\Exceptions\EntityNotFound
     */
    public function it_can_destroy_a_host()
    {
        $this->service->destroy(self::TEST_FQDN_2);

        $this->service->showOne(self::TEST_FQDN_2);
    }
}
 