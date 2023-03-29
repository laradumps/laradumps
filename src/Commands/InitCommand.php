<?php

namespace LaraDumps\LaraDumps\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\{File};
use LaraDumps\LaraDumps\Actions\{Config, ConsoleUrl};
use LaraDumps\LaraDumps\Commands\Concerns\RenderAscii;

class InitCommand extends Command
{
    use RenderAscii;

    protected $signature = 'ds:init {--no-interaction?} {--host=} {--port=} {--send_queries=} {--send_http_client_requests=} {--send_jobs=} {--send_commands=} {--send_cache=} {--send_logs=} {--send_livewire=} {--livewire_events=}{--livewire_validation=}  {--livewire_autoclear=} {--auto_invoke=} {--ide=}';

    protected $description = 'Initialize LaraDumps configuration';

    protected bool $isInteractive = true;

    public function handle(): int
    {
        $this->isInteractive = empty($this->option('no-interaction'));

        $this->welcome();

        $this->setHost();

        $this->thanks();

        return Command::SUCCESS;
    }

    private function welcome(): void
    {
        if ($this->isInteractive === false) {
            return;
        }

        $this->renderLogo();

        $this->line('Welcome & thank you for installing LaraDumps. This wizard will guide you through the basic setup.');
        $this->line("\nDownload LaraDumps app at: <comment>https://github.com/laradumps/app/releases</comment>");
        $this->line("\nFor more information and detailed setup instructions, access our <comment>documentation</comment> at: <comment>https://laradumps.dev/</comment> \n");
    }

    private function thanks(): void
    {
        if ($this->isInteractive === false) {
            return;
        }

        $this->line("\n📝 The <comment>.env</comment> file has been updated.\n");

        $this->line("\n\n⭐ Please consider <comment>starring</comment> our repository at <comment>https://github.com/laradumps/laradumps</comment>\n");
    }

    private function setHost(): self
    {
        $host = $this->option('host');

        if (empty($host) && $this->isInteractive) {
            $hosts = [
                '127.0.0.1',
                'host.docker.internal',
                '10.211.55.2',
                'other',
            ];

            $defaultHost = '127.0.0.1';

            //Homestead
            if (File::exists(base_path('Homestead.yaml'))) {
                $defaultHost = '10.211.55.2';
            }

            //Docker
            if (File::exists(base_path('docker-compose.yml'))) {
                $defaultHost = 'host.docker.internal';
            }

            //Add blank space to avoid auto-completing suggestion
            $defaultHost = (string) array_search($defaultHost, $hosts);

            $hosts = array_map(fn ($host) => ' ' . $host, $hosts);

            $host = $this->choice(
                'Select the App host address',
                $hosts,
                $defaultHost
            );

            if (is_string($host)) {
                $host = ltrim($host);
            }

            if ($host == 'other') {
                $host = $this->ask('Enter the App Host');
            }

            if ($host == 'host.docker.internal' && PHP_OS_FAMILY == 'Linux') {
                $docUrl = 'http://laradumps.dev/#/laravel/get-started/configuration?id=host';

                if ($this->confirm("\n❗<error>  IMPORTANT  </error>❗ You need to perform some extra configuration for Docker with Linux host. Read more at: <comment>{$docUrl}</comment>.\n\nBrowse the documentation now?") === true) {
                    ConsoleUrl::open($docUrl);
                }
            }
        }

        Config::set('host', strval($host));

        return $this;
    }
}
