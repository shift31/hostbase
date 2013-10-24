<?php

use Hostbase\Host\HostInterface;
use Symfony\Component\Yaml\Yaml;

class HostController extends BaseController
{

	public function __construct(HostInterface $hosts)
	{
		$this->hosts = $hosts;
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		// handle search
		if (Input::has('q')) {
			try {
				$hosts = $this->hosts->search(Input::get('q'), Input::has('size') ? Input::get('size') : 10000, true);

				if (Request::header('Accept') == 'application/yaml') {
					return Response::make(Yaml::dump($hosts), 200, array('Content-Type' => 'application/yaml'));
				} else {
					return Response::json($hosts);
				}
			} catch (Exception $e) {
				return Response::json($e->getMessage(), 500);
			}
		} else {
			if (Request::header('Accept') == 'application/yaml') {
				return Response::make(Yaml::dump($this->hosts->show()), 200, array('Content-Type' => 'application/yaml'));
			} else {
				return Response::json($this->hosts->show());
			}

		}
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if (Input::isJson()) {

			$data = Input::all();

		} elseif (Request::header('Content-Type') == 'application/yaml') {

			$data = Yaml::parse(Input::getContent());

		} else {
			return Response::json("Content-Type must be 'application/json' or 'application/yaml", 500);
		}

		try {
			$host = $this->hosts->store($data);

			if (Request::header('Accept') == 'application/yaml') {
				return Response::make(Yaml::dump($host), 200, array('Content-Type' => 'application/yaml'));
			} else {
				return Response::json($host);
			}
		} catch (Exception $e) {
			return Response::json($e->getMessage(), 500);
		}
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  string $fqdn
	 *
	 * @return Response
	 */
	public function show($fqdn)
	{
		try {
			if (Request::header('Accept') == 'application/yaml') {
				return Response::make(Yaml::dump($this->hosts->show($fqdn)), 200, array('Content-Type' => 'application/yaml'));
			} else {
				return Response::json($this->hosts->show($fqdn));
			}
		} catch (Exception $e) {
			return Response::json($e->getMessage(), 404);
		}
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  string $fqdn
	 *
	 * @return Response
	 */
	public function edit($fqdn)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  string $fqdn
	 *
	 * @return Response
	 */
	public function update($fqdn)
	{
		if (Input::isJson()) {

			$data = Input::all();

		} elseif (Request::header('Content-Type') == 'application/yaml') {

			$data = Yaml::parse(Input::getContent());

		} else {
			return Response::json("Content-Type must be 'application/json' or 'application/yaml", 500);
		}

		try {
			$updatedData = $this->hosts->update($fqdn, $data);

			if (Request::header('Accept') == 'application/yaml') {
				return Response::make(Yaml::dump($updatedData), 200, array('Content-Type' => 'application/yaml'));
			} else {
				return Response::json($updatedData);
			}
		} catch (Exception $e) {
			return Response::json($e->getMessage(), 500);
		}
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string $fqdn
	 *
	 * @return Response
	 */
	public function destroy($fqdn)
	{
		try {
			$this->hosts->destroy($fqdn);

			return Response::json("Deleted $fqdn");
		} catch (Exception $e) {
			return Response::json($e->getMessage(), 500);
		}

	}

}