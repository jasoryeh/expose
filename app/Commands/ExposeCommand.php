<?php

namespace App\Commands;

use Illuminate\Console\Command;

abstract class ExposeCommand extends Command
{

    public function getUserHome() {
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'];
        if (empty($home)) {
            throw new \RuntimeException("Failed to determine your user home directory!");
        }
        return $home;
    }

    public function path(array $paths): string {
        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    public function getConfigPath() {
        return $this->path([
            $this->getUserHome(),
            '.expose',
            'config.php',
        ]);
    }

    public function writeConfig(string $config_path) {
        $config_dir = dirname($config_path);
        if (!file_exists($config_dir) || !is_dir($config_dir)) {
            $mkdir_result = mkdir($config_dir, 0777, true);
            if (!$mkdir_result) {
                $this->error("Could not make an Expose configuration for you, please check that you have permissions to make the directory {$config_path}.");
                throw new \RuntimeException("Could not create a Expose configuration folder for you at {$config_path}!");
            }
        }

        $base_config = base_path('config/expose.php');
        $contents = file_get_contents($base_config);
        if ($contents === FALSE) {
            $this->error("Could not read default Expose configuration, please check you have permissions to {$base_config}.");
            throw new \RuntimeException("Could not access default Expose configuration at {$base_config}!");
        }

        $write_results = file_put_contents($config_path, $contents);
        if($write_results === FALSE) {
            $this->error("Could not write your Expose configuration, please check you have permissions to {$config_path}.");
            throw new \RuntimeException("Could not write to Expose configuration at {$config_path}!");
        }

        $this->info("Wrote Expose configuration to {$config_path}");
    }

    public function getConfig() {
        $config_path = $this->getConfigPath();

        if (!file_exists($config_path)) {
            $this->info("Expose configuration is not available at {$config_path}, making it for you...");
            $this->writeConfig($config_path);
        }

        return $config_path;
    }

    public function getToken() {
        return $this->option('auth') ?? config('expose.auth_token', '');
    }

    public function cwd() {
        return getcwd();
    }
}
