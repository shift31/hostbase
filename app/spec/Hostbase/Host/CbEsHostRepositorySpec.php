<?php namespace spec\Hostbase\Host;

use Basement\Client as BasementClient;
use Basement\data\Document;
use Basement\data\DocumentCollection;
use Elasticsearch\Client as ElasticsearchClient;
use Hostbase\Exceptions\NoSearchResults;
use Hostbase\Host\CbEsHostRepository;
use Hostbase\Host\Host;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;



class CbEsHostRepositorySpec extends ObjectBehavior
{

    function let(BasementClient $cb, ElasticsearchClient $es)
    {
        $this->beConstructedWith($cb, $es);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Hostbase\Host\CbEsHostRepository');
    }

    function it_can_return_an_array_of_host_ids_when_searching(ElasticsearchClient $es)
    {
        $es->search(Argument::any())->willReturn([
            'hits' => [
                'hits' => [
                    0 => [
                        '_id' => 'host_test.example.com'
                    ]
                ]
            ]
        ]);

        $this->search('test.example.com')->shouldReturn([
            'test.example.com'
        ]);
    }

    function it_can_return_an_array_of_host_entities_when_searching_for_complete_data(BasementClient $cb, ElasticsearchClient $es)
    {
       $es->search(Argument::any())->willReturn([
            'hits' => [
                'hits' => [
                    0 => [
                        '_id' => 'host_test.example.com'
                    ]
                ]
            ]
        ]);


        $cb->findByKey(['host_test.example.com'])->willReturn($this->stubHostDocumentCollection());

        $this->search('test.example.com', 10000, true)->shouldReturnArrayOfHosts('test.example.com');
    }

    function it_throws_an_exception_when_the_search_yields_no_results(ElasticsearchClient $es)
    {
        $es->search(Argument::any())->willReturn([
            'hits' => [
                'hits' => []
            ]
        ]);

        $this->shouldThrow(new NoSearchResults("No hosts matching 'test.example.com' were found"))->during('search', ['test.example.com']);
    }

    function getMatchers()
    {
        return [
            'returnArrayOfHosts' => function($hosts, $fqdn) {
                $host = $hosts[0];

                if (! $host instanceof Host) {
                    return false;
                }

                if ($host->getFqdn() != $fqdn) {
                    return false;
                }

                return true;
            }
        ];
    }

    function stubHostDocumentCollection()
    {
        $docCollection = new DocumentCollection();
        $docCollection[] = new Document(['key' => 'host_test.example.com', 'doc' => ['fqdn' => 'test.example.com']]);
        return $docCollection;
    }

    function stubHost()
    {
        return new Host('test.example.com');

    }
}
