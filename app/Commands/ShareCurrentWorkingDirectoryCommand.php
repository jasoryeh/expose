<?php

namespace App\Commands;

class ShareCurrentWorkingDirectoryCommand extends ShareCommand
{
    protected $signature = 'share-cwd {host?} {--subdomain=} {--auth=} {--basicAuth=} {--dns=} {--domain=}';

    public function handle()
    {
        $folderName = $this->detectName();
        $host = $this->prepareSharedHost($folderName.'.'.$this->detectTld());

        $this->input->setArgument('host', $host);

        if (! $this->option('subdomain')) {
            $this->input->setOption('subdomain', str_replace('.', '-', $folderName));
        }

        parent::handle();
    }

    protected function detectTld(): string
    {
        $valetConfigFile = $this->path([
            $this->getUserHome(),
            '.config',
            'valet',
            'config.json',
        ]);

        if (file_exists($valetConfigFile)) {
            $valetConfig = json_decode(file_get_contents($valetConfigFile));

            return $valetConfig->tld;
        }

        return config('expose.default_tld', 'test');
    }

    protected function detectName(): string
    {
        $projectPath = getcwd();
        $valetSitesPath = $this->path([
            $this->getUserHome(),
            '.config',
            'valet',
            'Sites',
        ]);

        if (is_dir($valetSitesPath)) {
            $site = collect(scandir($valetSitesPath))
            ->skip(2)
            ->map(function ($site) use ($valetSitesPath) {
                return $valetSitesPath.DIRECTORY_SEPARATOR.$site;
            })->mapWithKeys(function ($site) {
                return [$site => readlink($site)];
            })->filter(function ($sourcePath) use ($projectPath) {
                return $sourcePath === $projectPath;
            })
            ->keys()
            ->first();

            if ($site) {
                $projectPath = $site;
            }
        }

        return basename($projectPath);
    }

    protected function detectProtocol($host): string
    {
        $certificateFile = $this->path([
            $this->getUserHome(),
            '.config',
            'valet',
            'Certificates',
            $host.'.crt',
        ]);

        if (file_exists($certificateFile)) {
            return 'https://';
        }

        return config('expose.default_https', false) ? 'https://' : 'http://';
    }

    protected function prepareSharedHost($host): string
    {
        return $this->detectProtocol($host).$host;
    }
}
