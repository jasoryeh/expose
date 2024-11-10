<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install {--server=} {--token=} {--no-token} {--default-server} {--export}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Installation wizard for Expose.';

    public function promptForServer(): string {
        return !empty($this->option('server')) ? $this->option('server') :
            $this->askWithCompletion(
                "Press enter to use the default Expose server, or specify your own (e.g. expose.exmaple.com:443).",
                ['default'], 'default');
    }

    public function promptForToken(): string {
        return !empty($this->option('token')) ? $this->option('token') :
            $this->ask("Please specify your token. (if empty, just press enter).", "");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expose_executable = $this->getExposeCommandPath();
        $envs = [];
        $build_args = [];

        // check if user wants to use a custom expose server
        $expose_server = !empty($this->option('default-server')) ? 'default' : $this->promptForServer();
        if ($expose_server !== 'default') {
            if (empty($expose_server) || !str_contains($expose_server, ':')) {
                $this->error("Invalid expose server '$expose_server'");
                return 1;
            }
            $envs[] = "EXPOSE_SERVERS=main:$expose_server";
            $build_args[] = "--server=$expose_server";
        } else {
            $build_args[] = "--default-server";
        }

        // check for a token
        $token = !empty($this->option('no-token')) ? null : $this->promptForToken();
        if (!empty($token)) {
            $envs[] = "EXPOSE_AUTH_TOKEN=$token";
            $build_args[] = "--token=$token";
        } else {
            $build_args[] = "--no-token";
        }

        $cmd_env = implode(' ', $envs);
        $full_cmd = implode(' ', [$cmd_env, $expose_executable]);
        $alias = "alias expose=\"$full_cmd\"";

        $merge_args = implode(' ', $build_args);
        $install_cmd = implode(' ', ["php expose install --export", $merge_args, '>>', '~/.profile']);

        if (empty($this->option('export'))) {
            $this->info("To install Expose, you'll be adding the following to your shell profile:");
            $this->info("");
            $this->info("    ".$alias);
            $this->info("");
            $this->info("If this looks correct, run the following:");
            $this->info("");
            $this->info("    > ".$install_cmd);
            $this->info("");
        } else {
            $this->info($alias);
        }

        // todo: detect a .profile/.zprofile and just do the export.
    }


    public function getExposeCommandPath() {
        return base_path('expose');
    }
}
