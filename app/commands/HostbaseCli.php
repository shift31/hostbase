<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;
use Hostbase\HostImpl;

class HostbaseCli extends Command {

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
     * Create a new command instance.
     *
     * @return \HostbaseCli
     */
	public function __construct()
	{
        $this->hosts = new HostImpl();

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
            $data = json_decode($this->option('add'), true);

            //Log::debug(print_r($data, true));

            if (!is_array($data)) {
                $this->error('Missing JSON');
                exit(1);
            } else {
                $data['fqdn'] = $queryOrFqdn;
                try {
                    $this->hosts->store($data);
                    $this->info("Added '$queryOrFqdn'");
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        } elseif ($this->option('update')) {
            $data = json_decode($this->option('update'), true);

            //Log::debug(print_r($data, true));

            if (!is_array($data)) {
                $this->error('Missing JSON');
                exit(1);
            } else {
                try {
                    $this->hosts->update($queryOrFqdn, $data);
                    $this->info("Modified '$queryOrFqdn'");
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        } elseif ($this->option('delete')) {
            if ($this->confirm("Are you sure you want to delete '$queryOrFqdn'? [yes|no]", true)) {
               try {
                   $this->hosts->destroy($queryOrFqdn);
                   $this->info("Deleted $queryOrFqdn");
               } catch (Exception $e) {
                   $this->error($e->getMessage());
               }
            } else {
                exit;
            }
        } else {
            $query = $queryOrFqdn;
            $hosts = $this->hosts->search($query, $this->option('showdata'));

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
                $this->error('No hosts matching your query were found.');
            }
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
            array('showdata', null, InputOption::VALUE_NONE, 'Show all data for host(s).', null),
			array('add', null, InputOption::VALUE_REQUIRED, 'Add a host.', null),
            array('update', null, InputOption::VALUE_REQUIRED, 'Update a host.', null),
            array('delete', null, InputOption::VALUE_NONE, 'Delete a host.', null),
		);
	}

}