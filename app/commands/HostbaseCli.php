<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
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
	 * @return void
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

        if ($this->option('add') || $this->option('modify') || $this->option('remove')) {
            $fqdn = $queryOrFqdn;
        } else {
            $query = $queryOrFqdn;
            $hosts = $this->hosts->search($query, $this->option('showdata'));

            //Log::debug(print_r($hosts, true));

            if (count($hosts) > 0) {
                $output = '';

                foreach ($hosts as $host) {
                    if ($this->option('showdata')) {
                        foreach ($host as $key => $value) {
                            if ($key == 'fqdn') {
                                $output .= $value . PHP_EOL;
                            } else {
                                $output .= "\t$key: $value" . PHP_EOL;
                            }
                        }
                    } else {
                        $output .= $host . PHP_EOL;
                    }
                }

                $this->line($output);
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
			array('add', null, InputOption::VALUE_NONE, 'Add a host.', null),
            array('modify', null, InputOption::VALUE_NONE, 'Modify a host.', null),
            array('remove', null, InputOption::VALUE_NONE, 'Remove a host.', null),
		);
	}

}