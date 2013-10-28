<?php

use Symfony\Component\Yaml\Yaml;

abstract class ResourceControllerAbstract extends BaseController
{

	protected $resources;


	public function __construct($resources)
	{
		$this->resources = $resources;
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
				$resources = $this->resources->search(Input::get('q'), Input::has('size') ? Input::get('size') : 10000, true);

				if (Request::header('Accept') == 'application/yaml') {
					return Response::make(Yaml::dump($resources), 200, array('Content-Type' => 'application/yaml'));
				} else {
					return Response::json($resources);
				}
			} catch (Exception $e) {
				return Response::json($e->getMessage(), 500);
			}
		} else {
			if (Request::header('Accept') == 'application/yaml') {
				return Response::make(Yaml::dump($this->resources->show()), 200, array('Content-Type' => 'application/yaml'));
			} else {
				return Response::json($this->resources->show());
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
			$resource = $this->resources->store($data);

			if (Request::header('Accept') == 'application/yaml') {
				return Response::make(Yaml::dump($resource), 200, array('Content-Type' => 'application/yaml'));
			} else {
				return Response::json($resource);
			}
		} catch (Exception $e) {
			return Response::json($e->getMessage(), 500);
		}
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  string $id
	 *
	 * @return Response
	 */
	public function show($id)
	{
		try {
			if (Request::header('Accept') == 'application/yaml') {
				return Response::make(Yaml::dump($this->resources->show($id)), 200, array('Content-Type' => 'application/yaml'));
			} else {
				return Response::json($this->resources->show($id));
			}
		} catch (Exception $e) {
			return Response::json($e->getMessage(), 404);
		}
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  string $id
	 *
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  string $id
	 *
	 * @return Response
	 */
	public function update($id)
	{
		if (Input::isJson()) {

			$data = Input::all();

		} elseif (Request::header('Content-Type') == 'application/yaml') {

			$data = Yaml::parse(Input::getContent());

		} else {
			return Response::json("Content-Type must be 'application/json' or 'application/yaml", 500);
		}

		try {
			$updatedData = $this->resources->update($id, $data);

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
	 * @param  string $id
	 *
	 * @return Response
	 */
	public function destroy($id)
	{
		try {
			$this->resources->destroy($id);

			return Response::json("Deleted $id");
		} catch (Exception $e) {
			return Response::json($e->getMessage(), 500);
		}

	}

}