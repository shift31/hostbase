<?php

use Hostbase\Host\CbEsHostRepository;


class CbEsHostRepositoryTest extends TestCase {

    const TEST_FQDN = 'test.example.com';
    const TEST_FQDN_2 = 'test2.example.com';

    /**
     * @var CbEsHostRepository
     */
    protected $repo;


    public function setUp()
    {
        parent::setUp();

        $cb = App::make('basement');

        $es = App::make('elasticsearch');

        $this->repo = new CbEsHostRepository($cb, $es);

        $host = $this->repo->makeNewEntity();
        $host->setFqdn(self::TEST_FQDN);

        $this->repo->store($host);

        // give elasticsearch a second to index
        sleep(1);
    }


    public function tearDown()
    {
        $this->repo->destroy(self::TEST_FQDN);
    }


    /** @test */
    public function it_can_store_a_host()
    {
        $host = $this->repo->makeNewEntity();

        $host->setFqdn(self::TEST_FQDN_2);

        $hostFromRepo = $this->repo->store($host);

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
        $host = $this->repo->makeNewEntity();
        $host->setFqdn(self::TEST_FQDN);

        $this->repo->store($host);
    }


    /**
     * @test
     * @expectedException Hostbase\Host\HostMissingFqdn
     */
    public function it_throws_an_exception_if_host_is_missing_fqdn()
    {
        $host = $this->repo->makeNewEntity();
        $host->setData(['foo' => 'bar']);

        $this->repo->store($host);
    }


    /** @test */
    public function it_can_search_for_and_find_an_existing_host()
    {
        $hosts = $this->repo->search(self::TEST_FQDN);

        $this->assertContains(self::TEST_FQDN, $hosts);
    }


    /** @test */
    public function it_can_search_for_and_find_an_existing_host_and_show_its_data()
    {
        $hosts = $this->repo->search(self::TEST_FQDN, 10000, true);

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
        $this->repo->search('NOSUCHHOST.example.com');
    }


    /** @test */
    public function it_can_show_a_host()
    {
        $host = $this->repo->show(self::TEST_FQDN);
        $data = $host->getData();

        $this->assertEquals(self::TEST_FQDN, $data['fqdn']);
        $this->assertEquals('test', $data['hostname']);
        $this->assertEquals('example.com', $data['domain']);
    }


    /** @test */
    public function it_can_list_all_hosts()
    {
        $list = $this->repo->show();

        $this->assertContains(self::TEST_FQDN, $list);
    }


    /** @test */
    public function it_can_update_a_host()
    {
        $host = $this->repo->makeNewEntity();
        $host->setFqdn(self::TEST_FQDN);
        $host->setData(['foo' => 'bar']);

        $updatedHost = $this->repo->update($host);

        $this->assertEquals('bar', $updatedHost->getData()['foo']);
    }


    /** @test */
    public function it_can_encrypt_admin_passwords()
    {
        $data = [
            'fqdn' => self::TEST_FQDN,
            'adminCredentials' => [
                'password' => 'foo'
            ]
        ];

        $this->repo->encryptAdminPassword($data);

        $this->assertArrayHasKey('encryptedPassword', $data['adminCredentials']);
        $this->assertArrayNotHasKey('password', $data['adminCredentials']);
    }


    /** @test */
    public function it_can_decrypt_admin_passwords()
    {
        $data = [
            'fqdn' => self::TEST_FQDN,
            'adminCredentials' => [
                'encryptedPassword' => 'eyJpdiI6ImxNTWwzaElmZkRZTlpDdDRxSlhRd0RMYlFUTzkyUkVsSjdUOEdrNE0rM3c9IiwidmFsdWUiOiJoUWZvN2cxMXFWVEh0K0RiUTQrelhZRE0rR2g3eUNmbVJ5MWtwUXl6Nm9rPSIsIm1hYyI6IjIzOGU4ZjVkMjUwNmZlMTM3NDJkMTY4ZmJmOGQ5ZmUxNDA1Zjk3MTgyMDM4ZDMwMWNhOWNlMDljMzJmNmQ3NGEifQ=='
            ]
        ];

        $this->repo->decryptAdminPassword($data);

        $this->assertArrayHasKey('password', $data['adminCredentials']);
        $this->assertArrayNotHasKey('encryptedPassword', $data['adminCredentials']);
    }


    /**
     * @test
     * @expectedException \Hostbase\Entity\Exceptions\EntityNotFound
     */
    public function it_can_destroy_a_host()
    {
        $this->repo->destroy(self::TEST_FQDN_2);

        $this->repo->show(self::TEST_FQDN_2);
    }
}
 