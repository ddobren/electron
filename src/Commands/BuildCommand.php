<?php

namespace Native\Electron\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Native\Electron\Facades\Updater;

class BuildCommand extends Command
{
    protected $signature = 'native:build';

    public function handle()
    {
        $this->info('Building NativePHP app...');

        // Step 1: Update npm dependencies
        $this->runProcess(__DIR__.'/../../resources/js/', 'npm update');

        // Step 2: Install composer dependencies without dev packages
        $this->runProcess(base_path(), 'composer install --no-dev');

        // Step 3: Build the app using npm
        $this->runProcess(__DIR__.'/../../resources/js/', 'npm run build:mac-arm');
    }

    protected function runProcess(string $path, string $command)
    {
        Process::path($path)
            ->env($this->getEnvironmentVariables())
            ->run($command, function (string $type, string $output) {
                echo $output;
            });
    }

    protected function getEnvironmentVariables()
    {
        return array_merge(
            [
                'APP_PATH' => base_path(),
                'APP_URL' => config('app.url'),
                'NATIVEPHP_BUILDING' => true,
                'NATIVEPHP_PHP_BINARY_PATH' => base_path('vendor/nativephp/php-bin/bin/mac'),
                'NATIVEPHP_CERTIFICATE_FILE_PATH' => base_path('vendor/nativephp/php-bin/cacert.pem'),
                'NATIVEPHP_APP_NAME' => config('app.name'),
                'NATIVEPHP_APP_ID' => config('nativephp.app_id'),
                'NATIVEPHP_APP_VERSION' => config('nativephp.version'),
                'NATIVEPHP_APP_FILENAME' => Str::slug(config('app.name')),
                'NATIVEPHP_APP_AUTHOR' => config('nativephp.author'),
                'NATIVEPHP_UPDATER_CONFIG' => json_encode(Updater::builderOptions()),
            ],
            Updater::environmentVariables()
        );
    }
}
