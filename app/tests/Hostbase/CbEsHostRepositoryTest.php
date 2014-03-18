<?php

use Hostbase\Host\CbEsHostRepository;


class CbEsHostRepositoryTest extends TestCase {

    const TEST_FQDN = 'test.example.com';

    /**
     * @var CbEsHostRepository
     */
    protected $repo;


    public function setUp()
    {
        parent::setUp();

        $this->repo = new CbEsHostRepository();
    }


    /** @test */
    public function it_can_store_a_host()
    {
        $this->destroyTestHost();

        $data = ['fqdn' => self::TEST_FQDN];

        $host = $this->repo->store($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $host[$key]);
        }
    }


    /**
     * @test
     * @expectedException Hostbase\Exceptions\ResourceAlreadyExistsException
     */
    public function it_throws_an_exception_if_host_already_exists()
    {
        $data = ['fqdn' => self::TEST_FQDN];

        $this->repo->store($data);
    }


    /**
     * @test
     * @expectedException Hostbase\Host\HostMissingFqdnException
     */
    public function it_throws_an_exception_if_host_is_missing_fqdn()
    {
        $data = ['foo' => 'bar'];

        $this->repo->store($data);
    }

    /** @test */
    public function it_can_search_for_and_find_an_existing_host()
    {
        // give elasticsearch a second to index
        sleep(1);

        $hosts = $this->repo->search(self::TEST_FQDN);

        $this->assertContains(self::TEST_FQDN, $hosts);
    }

    /** @test */
    public function it_can_search_for_and_find_an_existing_host_and_show_its_data()
    {
        $hosts = $this->repo->search(self::TEST_FQDN, 10000, true);

        $this->assertEquals('test', $hosts[0]['hostname']);
        $this->assertEquals('example.com', $hosts[0]['domain']);
    }


    /**
     * @test
     * @expectedException \Hostbase\Exceptions\NoSearchResultsException
     */
    public function it_throws_an_exception_if_there_are_no_search_results()
    {
        $this->repo->search('NOSUCHHOST.example.com');
    }


    /** @test */
    public function it_can_show_a_host()
    {
        $host = $this->repo->show(self::TEST_FQDN);

        $this->assertEquals(self::TEST_FQDN, $host['fqdn']);
        $this->assertEquals('test', $host['hostname']);
        $this->assertEquals('example.com', $host['domain']);
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
        $host = $this->repo->update(self::TEST_FQDN, ['foo' => 'bar']);

        $this->assertEquals('bar', $host['foo']);
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
     * @expectedException \Hostbase\Exceptions\ResourceNotFoundException
     */
    public function it_can_destroy_a_host()
    {
        $this->repo->destroy(self::TEST_FQDN);

        $this->repo->show(self::TEST_FQDN);
    }


    protected function destroyTestHost()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $cbConnection = Cb::connection();

        if (!$cbConnection) {
            throw new \Exception("No Couchbase connection");
        }

        $cbConnection->delete('host_' . self::TEST_FQDN);
    }
}
 