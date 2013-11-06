<?php

use Hostbase\Host\HostInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;


class HostbaseCli extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'hostbase';


	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'View and manipulate your host database.';


	/**
	 * @param HostInterface $hosts
	 */
	public function __construct(HostInterface $hosts)
	{
		$this->hosts = $hosts;

		parent::__construct();
	}


	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$queryOrFqdn = $this->argument('query|fqdn');

		if ($this->option('add')) {
			$this->add($queryOrFqdn);
		} elseif ($this->option('update')) {
			$this->update($queryOrFqdn);
		} elseif ($this->option('delete')) {
			$this->delete($queryOrFqdn);
		} else {
			$this->search($queryOrFqdn);
		}
	}


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('query|fqdn', InputArgument::REQUIRED, 'A query or FQDN .'),
		);
	}


	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('limit', null, InputOption::VALUE_REQUIRED, 'Limit size of result set.', null),
			array('showdata', null, InputOption::VALUE_NONE, 'Show all data for host(s).', null),
			array('add', null, InputOption::VALUE_REQUIRED, 'Add a host.', null),
			array('update', null, InputOption::VALUE_REQUIRED, 'Update a host.', null),
			array('delete', null, InputOption::VALUE_NONE, 'Delete a host.', null),
		);
	}


	/**
	 * @param $query
	 */
	protected function search($query)
	{
		$limit = $this->option('limit') > 0 ? $this->option('limit') : 10000;

		$hosts = $this->hosts->search($query, $limit, $this->option('showdata'));

		//Log::debug(print_r($hosts, true));

		if (count($hosts) > 0) {
			foreach ($hosts as $host) {
				if ($this->option('showdata')) {
					$this->info($host['fqdn']);
					$this->line(Yaml::dump($host, 2));
				} else {
					$this->info($host);
				}
			}
		} else {
			$this->error("No hosts matching '$query' were found.");
		}
	}


	/**
	 * @param $fqdn
	 */
	protected function add($fqdn)
	{
		$data = json_decode($this->option('add'), true);

		//Log::debug(print_r($data, true));

		if (!is_array($data)) {
			$this->error('Missing JSON');
			exit(1);
		} else {
			$data['fqdn'] = $fqdn;

			try {
				$this->hosts->store($data);
				$this->info("Added '$fqdn'");
			} catch (Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}


	/**
	 * @param $fqdn
	 */
	protected function update($fqdn)
	{
		$data = json_decode($this->option('update'), true);

		//Log::debug(print_r($data, true));

		if (!is_array($data)) {
			$this->error('Missing JSON');
			exit(1);
		} else {
			try {
				$this->hosts->update($fqdn, $data);
				$this->info("Modified '$fqdn'");
			} catch (Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}


	/**
	 * @param $fqdn
	 */
	protected function delete($fqdn)
	{
		if ($this->confirm("Are you sure you want to delete '$fqdn'? [yes|no]")) {
			try {
				$this->hosts->destroy($fqdn);
				$this->info("Deleted $fqdn");
			} catch (Exception $e) {
				$this->error($e->getMessage());
			}
		} else {
			exit;
		}
	}

}