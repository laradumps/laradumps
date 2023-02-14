<?php

namespace LaraDumps\LaraDumps\Commands;

use Exception;
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

        $this->setHost()
            ->setPort()
            ->setQueries()
            ->setHttpClientRequests()
            ->setJobs()
            ->setCommands()
            ->setCache()
            ->setLogs()
            ->setLivewire()
            ->setLivewireEvents()
            ->setLivewireAutoClear()
            ->setAutoInvoke()
            ->setPreferredIde();

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

        $this->line("\n🎉 <fg=green>Setup completed successfully!</> If you want to re-use this same configuration in other Laravel projects, simply run:\n");

        $this->line('<fg=cyan>   php artisan ds:init --no-interaction --host=' . Config::get('host')
            . ' --port=' . Config::get('port')
            . ' --send_queries=' . (Config::get('send_queries') ? 'true' : 'false')
            . ' --send_http_client_requests=' . (Config::get('send_http_client_requests') ? 'true' : 'false')
            . ' --send_jobs=' . (Config::get('send_jobs') ? 'true' : 'false')
            . ' --send_commands=' . (Config::get('send_commands') ? 'true' : 'false')
            . ' --send_cache=' . (Config::get('send_cache') ? 'true' : 'false')
            . ' --send_logs=' . (Config::get('send_log_applications') ? 'true' : 'false')
            . ' --send_livewire=' . (Config::get('send_livewire_components') ? 'true' : 'false')
            . ' --livewire_events=' . (Config::get('send_livewire_events') ? 'true' : 'false')
            . ' --livewire_validation=' . (Config::get('send_livewire_failed_validation.enabled') ? 'true' : 'false')
            . ' --livewire_autoclear=' . (Config::get('auto_clear_on_page_reload') ? 'true' : 'false')
            . ' --auto_invoke=' . (Config::get('auto_invoke_app') ? 'true' : 'false')
            . ' --ide=' . Config::get('preferred_ide')
            . "</>\n\n");

        $this->line("\n\n⭐ Please consider <comment>starring</comment> our repository at <comment>https://github.com/laradumps/laradumps</comment>\n");

        ds('It works! Thank you for using LaraDumps!')->toScreen('🤖 Setup');
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

    private function setPort(): self
    {
        $port = $this->option('port');

        if (empty($port) && $this->isInteractive) {
            $port = $this->ask('Enter the App Port', '9191');
        }

        Config::set('port', strval($port));

        return $this;
    }

    private function setQueries(): self
    {
        $sendQueries = $this->option('send_queries');

        if (empty($sendQueries) && $this->isInteractive) {
            $sendQueries = $this->confirm('Allow dumping <comment>SQL Queries</comment> to the App?', true);
        }

        $sendQueries = filter_var($sendQueries, FILTER_VALIDATE_BOOLEAN);

        Config::set('send_queries', ($sendQueries ? 'true' : 'false'));

        return $this;
    }

    private function setLogs(): self
    {
        $sendLogs = $this->option('send_logs');

        if (empty($sendLogs) && $this->isInteractive) {
            $sendLogs = $this->confirm('Allow dumping <comment>Laravel Logs</comment> to the App?', true);
        }

        $sendLogs = filter_var($sendLogs, FILTER_VALIDATE_BOOLEAN);

        Config::set('send_log_applications', ($sendLogs ? 'true' : 'false'));

        return $this;
    }

    private function setLivewire(): self
    {
        $sendLivewire = $this->option('send_livewire');

        if (empty($sendLivewire) && $this->isInteractive) {
            $sendLivewire = $this->confirm('Allow dumping <comment>Livewire components</comment> to the App?', true);
        }

        $sendLivewire = filter_var($sendLivewire, FILTER_VALIDATE_BOOLEAN);

        Config::set('send_livewire_components', ($sendLivewire ? 'true' : 'false'));

        return $this;
    }

    private function setLivewireEvents(): self
    {
        $sendLivewireEvents = $this->option('livewire_events');

        if (empty($sendLivewireEvents) && $this->isInteractive) {
            $sendLivewireEvents = $this->confirm('Allow dumping <comment>Livewire Events</comment> & <comment>Browser Events (dispatch)</comment> to the App?', true);
        }

        $sendLivewireEvents = filter_var($sendLivewireEvents, FILTER_VALIDATE_BOOLEAN);

        Config::set('send_livewire_events', ($sendLivewireEvents ? 'true' : 'false'));

        return $this;
    }

    private function setLivewireAutoClear(): self
    {
        $allowLivewireAutoClear = $this->option('livewire_autoclear');

        if (empty($allowLivewireAutoClear) && $this->isInteractive) {
            $allowLivewireAutoClear = $this->confirm('Enable <comment>Auto-clear</comment> APP History on page reload?', false);
        }

        $allowLivewireAutoClear = filter_var($allowLivewireAutoClear, FILTER_VALIDATE_BOOLEAN);

        Config::set('auto_clear_on_page_reload', ($allowLivewireAutoClear ? 'true' : 'false'));

        return $this;
    }

    private function setAutoInvoke(): self
    {
        $autoInvoke = $this->option('auto_invoke');

        if (empty($autoInvoke) && $this->isInteractive) {
            $autoInvoke = $this->confirm('Would you like to invoke the App window on every Dump?', true);
        }

        $autoInvoke = filter_var($autoInvoke, FILTER_VALIDATE_BOOLEAN);

        Config::set('auto_invoke_app', ($autoInvoke ? 'true' : 'false'));

        return $this;
    }

    private function ideConfigList(): array
    {
        $configFilePath = __DIR__ . '/../../config/laradumps.php';
        $configFilePath = str_replace('/', DIRECTORY_SEPARATOR, $configFilePath);

        if (!File::exists($configFilePath)) {
            throw new Exception("LaraDumps config file doesn't exist.");
        }

        $ideList = include($configFilePath);

        return array_keys((array) $ideList['ide_handlers']);
    }

    private function setPreferredIde(): self
    {
        $ide = $this->option('ide');

        $ideList = $this->ideConfigList();

        if ($this->isInteractive && empty($ide)) {
            $ide = $this->choice(
                'What is your preferred IDE for this project?',
                $ideList,
                'phpstorm'
            );

            if ($ide == 'vscode_remote') {
                $docUrl = 'https://laradumps.dev/#/laravel/get-started/configuration?id=remote-vscode-wsl2';

                if ($this->confirm("\n❗<error>  IMPORTANT  </error>❗ You need to perform some extra configuration for VS Code Remote to work properly. Read more at: <comment>{$docUrl}</comment>.\n\nBrowse the documentation now?") === true) {
                    ConsoleUrl::open($docUrl);
                }
            }
        }

        if (!in_array($ide, $ideList)) {
            throw new Exception('Invalid IDE');
        }

        Config::set('preferred_ide', strval($ide));

        return $this;
    }

    private function setHttpClientRequests(): self
    {
        $httpRequests = $this->option('send_http_client_requests');

        if (empty($httpRequests) && $this->isInteractive) {
            $httpRequests = $this->confirm('Allow dumping <comment>HTTP Client Requests</comment> to the App?', true);
        }

        $httpRequests = filter_var($httpRequests, FILTER_VALIDATE_BOOLEAN);

        Config::set('send_http_client_requests', ($httpRequests ? 'true' : 'false'));

        return $this;
    }

    private function setJobs(): self
    {
        $sendJobs = $this->option('send_jobs');

        if (empty($sendJobs) && $this->isInteractive) {
            $sendJobs = $this->confirm('Allow dumping <comment>Jobs</comment> to the App?', true);
        }

        $sendJobs = filter_var($sendJobs, FILTER_VALIDATE_BOOLEAN);

        Config::set('send_jobs', ($sendJobs ? 'true' : 'false'));

        return $this;
    }

    private function setCache(): self
    {
        $sendCache = $this->option('send_cache');

        if (empty($sendCache) && $this->isInteractive) {
            $sendCache = $this->confirm('Allow dumping <comment>Cache</comment> to the App?', true);
        }

        $sendCache = filter_var($sendCache, FILTER_VALIDATE_BOOLEAN);

        Config::set('send_cache', ($sendCache ? 'true' : 'false'));

        return $this;
    }

    private function setCommands(): self
    {
        $sendCommands = $this->option('send_commands');

        if (empty($sendCommands) && $this->isInteractive) {
            $sendCommands = $this->confirm('Allow dumping <comment>Commands</comment> to the App?', true);
        }

        $sendCommands = filter_var($sendCommands, FILTER_VALIDATE_BOOLEAN);

        Config::set('send_commands', ($sendCommands ? 'true' : 'false'));

        return $this;
    }
}
