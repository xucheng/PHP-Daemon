<?php

class App_ExampleWorkers extends Core_Daemon
{
    protected $loop_interval = 3;
    public $count = 0;

    protected function load_plugins()
    {
        $this->plugin('Lock_File');

        $this->plugin('Plugin_Ini', array(), 'ini');
        $this->ini->filename = BASE_PATH . '/App/config.ini';
        $this->ini->required_sections = array('example_section');
    }

    protected function load_workers()
    {
        $this->worker('example', new App_MyWorker());
        $this->example->timeout(5);
        $this->example->workers(3);

        $that = $this;
        $this->example->onTimeout(function($call) use($that) {
            $that->log("Job Timed Out!");
            $that->log("Method: " . $call->method);
        });
    }


    protected function setup()
    {

        if ($this->is_parent())
        {

            // Call the worker when you pass the SIGUSR2 signal to the daemon.
            // You can use the script in /scripts/usr2_signal or just do:  kill -12 [pid]

            $that = $this; // PHP 5.3 closure hack. Fixed in 5.4

            $this->on(Core_Daemon::ON_SIGNAL, function($signal) use($that) {

                $array = array(1001, true, "I like beagles!");

                $that->count++;
                if ($signal == SIGUSR2) {
                    $that->example->doooit($that->count, $array);
                }
            });
        }
    }


    protected function execute()
    {
        $this->log('Execute..');
    }

    protected function log_file()
    {
        $dir = '/var/log/daemons/example';
        if (@file_exists($dir) == false)
            @mkdir($dir, 0777, true);

        if (@is_writable($dir) == false)x
            $dir = BASE_PATH . '/example_logs';

        return $dir . '/log_' . date('Ymd');
    }
}