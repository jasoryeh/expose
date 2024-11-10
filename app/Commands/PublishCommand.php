<?php

namespace App\Commands;

class PublishCommand extends ExposeCommand
{
    protected $signature = 'publish {--force}';

    protected $description = 'Publish the expose configuration file';

    public function handle()
    {
        $configFile = $this->getConfigPath();

        if (! $this->option('force') && file_exists($configFile)) {
            $this->error('Expose configuration file already exists at '.$configFile);

            return;
        }

        $this->writeConfig($configFile);

        $this->info('Published expose configuration file to: '.$configFile);
    }
}
