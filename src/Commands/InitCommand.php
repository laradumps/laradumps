<?php

namespace LaraDumps\LaraDumps\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{Artisan, File};
use LaraDumps\LaraDumps\Actions\UpdateEnv;
use LaraDumps\LaraDumps\Actions\{ConsoleUrl, ListCodeEditors};
use LaraDumps\LaraDumps\Commands\Concerns\{RenderAscii};

class InitCommand extends Command
{
    use RenderAscii;

    protected $signature = 'ds:init {--no-interaction?} {--host=} {--port=} {--send_queries=} {--send_logs=} {--send_livewire=} {--livewire_events=}{--livewire_validation=}  {--livewire_autoclear=} {--auto_invoke=} {--ide=}';

    protected $description = 'Initialize LaraDumps configuration';

    protected bool $isInteractive = true;

    public function handle(): int
    {
        $this->isInteractive = empty($this->option('no-interaction'));

        $this->publishConfig();

        $this->welcome();

        $this->setHost()
            ->setPort()
            ->setQueries()
            ->setLogs()
            ->setLivewire()
            ->setLivewireEvents()
            ->setLivewireValidation()
            ->setLivewireAutoClear()
            ->setAutoInvoke()
            ->setPreferredIde();

        $this->thanks();

        return Command::SUCCESS;
    }

    private function publishConfig(): void
    {
        if ($this->isInteractive  && File::exists(config_path('laradumps.php'))) {
            if ($this->confirm('The config file <comment>laradumps.php</comment> already exists. Delete it?') === true) {
                File::delete(config_path('laradumps.php'));
            }
        }

        $this->call('vendor:publish', ['--tag' => 'laradumps-config']);
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

        $this->line('<fg=cyan>   php artisan ds:init --no-interaction --host=' . config('laradumps.host')
                    . ' --port=' . config('laradumps.port')
                    . ' --send_queries=' . (config('laradumps.send_queries') ? 'true' : 'false')
                    . ' --send_logs=' . (config('laradumps.send_log_applications') ? 'true' : 'false')
                    . ' --send_livewire=' . (config('laradumps.send_livewire_components') ? 'true' : 'false')
                    . ' --livewire_events=' . (config('laradumps.send_livewire_events') ? 'true' : 'false')
                    . ' --livewire_validation=' . (config('laradumps.send_livewire_failed_validation.enabled') ? 'true' : 'false')
                    . ' --livewire_autoclear=' . (config('laradumps.auto_clear_on_page_reload') ? 'true' : 'false')
                    . ' --auto_invoke=' . (config('laradumps.auto_invoke_app') ? 'true' : 'false')
                    . ' --ide=' . config('laradumps.preferred_ide')
                    . "</>\n\n");

        $this->line("\n\n⭐ Please consider <comment>starring</comment> our repository at <comment>https://github.com/laradumps/laradumps</comment>\n");

        ds('It works! Thank you for using LaraDumps!')->toScreen('🤖 Setup');
    }

    private function setHost(): self
    {
        $host = $this->option('host');

        if (empty($host) && $this->isInteractive) {
            $hosts =  [
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

            $host =  $this->choice(
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

            if ($host == 'host.docker.internal' && PHP_OS_FAMILY ==  'Linux') {
                $docUrl = 'http://laradumps.dev/#/laravel/get-started/configuration?id=host';

                if ($this->confirm("\n❗<error>  IMPORTANT  </error>❗ You need to perform some extra configuration for Docker with Linux host. Read more at: <comment>{$docUrl}</comment>.\n\nBrowse the documentation now?") === true) {
                    ConsoleUrl::open($docUrl);
                }
            }
        }

        config()->set('laradumps.host', $host);
        UpdateEnv::handle('DS_APP_HOST', strval($host));

        return $this;
    }

    private function setPort(): self
    {
        $port = $this->option('port');

        if (empty($port) && $this->isInteractive) {
            $port = $this->ask('Enter the App Port', '9191');
        }

        config()->set('laradumps.port', $port);
        UpdateEnv::handle('DS_APP_PORT', strval($port));

        return $this;
    }

    private function setQueries(): self
    {
        $sendQueries =  $this->option('send_queries');

        if (empty($sendQueries) && $this->isInteractive) {
            $sendQueries = $this->confirm('Allow dumping <comment>SQL Queries</comment> to the App?', true);
        }

        $sendQueries = filter_var($sendQueries, FILTER_VALIDATE_BOOLEAN);

        config()->set('laradumps.send_queries', boolval($sendQueries));
        UpdateEnv::handle('DS_SEND_QUERIES', ($sendQueries ? 'true' : 'false'));

        return $this;
    }

    private function setLogs(): self
    {
        $sendLogs =  $this->option('send_logs');

        if (empty($sendLogs) && $this->isInteractive) {
            $sendLogs = $this->confirm('Allow dumping <comment>Laravel Logs</comment> to the App?', true);
        }

        $sendLogs = filter_var($sendLogs, FILTER_VALIDATE_BOOLEAN);

        config()->set('laradumps.send_log_applications', boolval($sendLogs));
        UpdateEnv::handle('DS_SEND_LOGS', ($sendLogs ? 'true' : 'false'));

        return $this;
    }

    private function setLivewire(): self
    {
        $sendLivewire =  $this->option('send_livewire');

        if (empty($sendLivewire) && $this->isInteractive) {
            $sendLivewire = $this->confirm('Allow dumping <comment>Livewire components</comment> to the App?', true);
        }

        $sendLivewire = filter_var($sendLivewire, FILTER_VALIDATE_BOOLEAN);

        config()->set('laradumps.send_livewire_components', boolval($sendLivewire));
        UpdateEnv::handle('DS_SEND_LIVEWIRE_COMPONENTS', ($sendLivewire ? 'true' : 'false'));

        return $this;
    }

    private function setLivewireEvents(): self
    {
        $sendLivewireEvents =  $this->option('livewire_events');

        if (empty($sendLivewireEvents) && $this->isInteractive) {
            $sendLivewireEvents = $this->confirm('Allow dumping <comment>Livewire Events</comment> & <comment>Browser Events (dispatch)</comment> to the App?', true);
        }

        $sendLivewireEvents = filter_var($sendLivewireEvents, FILTER_VALIDATE_BOOLEAN);

        config()->set('laradumps.send_livewire_events', boolval($sendLivewireEvents));
        UpdateEnv::handle('DS_LIVEWIRE_EVENTS', ($sendLivewireEvents ? 'true' : 'false'));

        config()->set('laradumps.send_livewire_dispatch', boolval($sendLivewireEvents));
        UpdateEnv::handle('DS_LIVEWIRE_DISPATCH', ($sendLivewireEvents ? 'true' : 'false'));

        return $this;
    }

    private function setLivewireValidation(): self
    {
        $sendLivewireValidation =  $this->option('livewire_validation');

        if (empty($sendLivewireValidation) && $this->isInteractive) {
            $sendLivewireValidation = $this->confirm('Allow dumping <comment>Livewire failed validation</comment> to the App?', true);
        }

        $sendLivewireValidation = filter_var($sendLivewireValidation, FILTER_VALIDATE_BOOLEAN);

        config()->set('laradumps.send_livewire_failed_validation.enabled', boolval($sendLivewireValidation));
        UpdateEnv::handle('DS_SEND_LIVEWIRE_FAILED_VALIDATION', ($sendLivewireValidation ? 'true' : 'false'));

        return $this;
    }

    private function setLivewireAutoClear(): self
    {
        $allowLivewireAutoClear =  $this->option('livewire_autoclear');

        if (empty($allowLivewireAutoClear) && $this->isInteractive) {
            $allowLivewireAutoClear = $this->confirm('Enable <comment>Auto-clear</comment> APP History on page reload?', false);
        }

        $allowLivewireAutoClear = filter_var($allowLivewireAutoClear, FILTER_VALIDATE_BOOLEAN);

        config()->set('laradumps.auto_clear_on_page_reload', boolval($allowLivewireAutoClear));
        UpdateEnv::handle('DS_AUTO_CLEAR_ON_PAGE_RELOAD', ($allowLivewireAutoClear ? 'true' : 'false'));

        return $this;
    }

    private function setAutoInvoke(): self
    {
        $autoInvoke =  $this->option('auto_invoke');

        if (empty($autoInvoke) && $this->isInteractive) {
            $autoInvoke = $this->confirm('Would you like to invoke the App window on every Dump?', true);
        }

        $autoInvoke = filter_var($autoInvoke, FILTER_VALIDATE_BOOLEAN);

        config()->set('laradumps.auto_invoke_app', boolval($autoInvoke));
        UpdateEnv::handle('DS_AUTO_INVOKE_APP', ($autoInvoke ? 'true' : 'false'));

        return $this;
    }

    private function setPreferredIde(): self
    {
        $ide     =  $this->option('ide');
        $ideList =  ListCodeEditors::handle();

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

        if (!in_array($ide, array_keys($ideList))) {
            throw new Exception('Invalid IDE');
        }

        config()->set('laradumps.preferred_ide', $ide);
        UpdateEnv::handle('DS_PREFERRED_IDE', strval($ide));

        return $this;
    }
}
