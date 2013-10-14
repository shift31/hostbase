<?php

use Hostbase\HostInterface;

class HostController extends \BaseController {

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
            return Response::json($this->hosts->search(Input::get('q'), true));
        } else {
            return Response::json($this->hosts->get());
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
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $fqdn
	 * @return Response
	 */
	public function show($fqdn)
    {
		$host = $this->hosts->get($fqdn);

        if ($host != null) {
            return Response::json($host);
        } else {
            return Response::json("Host '$fqdn' not found'", 404);
        }
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  string  $fqdn
	 * @return Response
	 */
	public function edit($fqdn)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  string  $fqdn
	 * @return Response
	 */
	public function update($fqdn)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string  $fqdn
	 * @return Response
	 */
	public function destroy($fqdn)
	{
		//
	}

}