<?php

use Hostbase\HostInterface;

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

				return Response::json($hosts);
			} catch (Exception $e) {
				return Response::json($e->getMessage(), 500);
			}
		} else {
			return Response::json($this->hosts->show());
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

			try {
				$this->hosts->store($data);

				return Response::json($data);
			} catch (Exception $e) {
				return Response::json($e->getMessage(), 500);
			}
		} else {
			return Response::json("Content-Type must be 'application/json'", 500);
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
			return Response::json($this->hosts->show($fqdn));
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

			try {
				$updatedData = $this->hosts->update($fqdn, $data);

				return Response::json($updatedData);
			} catch (Exception $e) {
				return Response::json($e->getMessage(), 500);
			}
		} else {
			return Response::json("Content-Type must be 'application/json'", 500);
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