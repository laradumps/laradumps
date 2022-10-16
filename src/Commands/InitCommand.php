<?php

namespace LaraDumps\LaraDumps\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaraDumps\LaraDumps\Actions\ListConfigKeys;
use LaraDumps\LaraDumps\Actions\{ConsoleUrl, ListCodeEditors, SuggestAppHost, UpdateEnv};
use LaraDumps\LaraDumps\Commands\Concerns\{RenderAscii};

class InitCommand extends Command
{
    use RenderAscii;

    protected $signature = 'ds:init {--no-interaction?}';

    protected $description = 'Initialize LaraDumps configuration';

    protected bool $isInteractive = true;

    /*** @var $configKeys Collection<int, non-empty-array<string, array|bool|string>> */
    protected Collection $configKeys;

    public function __construct()
    {
        $this->configKeys = ListConfigKeys::handle();

        // Add arguments to signature
        $this->configKeys->each(function ($key) {
            $key['param'] = strval($key['param']);
            $this->signature .=  " {--{$key['param']}=} ";
        });

        parent::__construct();
    }

    public function handle(): int
    {
        $this->isInteractive = empty($this->option('no-interaction'));

        $this->welcome();

        $this->publishConfig();

        $this->setup();

        $this->thanks();

        return Command::SUCCESS;
    }

    private function publishConfig(): void
    {
        if ($this->isInteractive  && File::exists(config_path('laradumps.php'))) {
            $this->line(PHP_EOL);

            $this->line('Publishing config file...');

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
    }

    private function thanks(): void
    {
        if ($this->isInteractive === false) {
            return;
        }

        ds('It works! Thank you for using LaraDumps!')->toScreen('🤖 Setup');

        $this->line(PHP_EOL . '🎉 <info>Setup completed successfully!</info> To configure LaraDumps vist: <comment>' . config('app.url') . '/laradumps</comment>');

        $this->line(PHP_EOL . "⭐ <info>Support LaraDumps!</info> Star our repository at: <comment>https://github.com/laradumps/laradumps</comment>\n");
    }

    private function setup(): self
    {
        $this->configKeys->each(
            function ($key) {
                if (is_array($key)
                    && key_exists('param', $key)
                    && key_exists('config_key', $key)
                    && key_exists('default_value', $key)
                ) {
                    $value = $this->option(strval($key['param'])) ?? $value = $key['default_value'];

                    if (is_bool($key['default_value'])) {
                        $value = boolval(filter_var($value, FILTER_VALIDATE_BOOLEAN));
                    }

                    config()->set(strval($key['config_key']), $value);

                    UpdateEnv::handle(strval($key['env_key']), $value);
                }
            }
        );

        $this->setHost()->setIde();

        return $this;
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

            //Add blank space to avoid auto-completing suggestion
            $defaultHost = (string) array_search(SuggestAppHost::handle(), $hosts);

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

    private function setIde(): self
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
