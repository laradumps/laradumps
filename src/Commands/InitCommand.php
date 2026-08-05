<?php

namespace LaraDumps\LaraDumps\Commands;

use Illuminate\Console\Command;
use LaraDumps\LaraDumps\Actions\{AppendLaradumpsYamlToGitignore, DefaultConfig};
use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'ds:init',
    description: 'Generate the "laradumps.yaml" file in the base path of the application',
    hidden: false
)]
class InitCommand extends Command
{
    protected $signature = 'ds:init {pwd=0}';

    public function handle(): void
    {
        /** @var string $pwd */
        $pwd = $this->argument('pwd');

        if ($pwd == '0' && isset($_ENV['IGNITION_LOCAL_SITES_PATH'])) {
            $pwd = $_ENV['IGNITION_LOCAL_SITES_PATH'];
        }

        if (Config::exists()) {
            ds('Welcome back to the LaraDumps!');

            $this->components->info('laradumps.yaml has already been published');

            return;
        }

        $publish = Config::publish(
            pwd: $pwd.DIRECTORY_SEPARATOR,
            filepath: Config::baseConfigPath()
        );

        if (! $publish) {
            $this->components->error('Failed to publish the laradumps.yaml file. Please check your permissions or the provided path.');

            return;
        }

        $newYaml = appBasePath().'laradumps.yaml';

        $mergedYaml = DefaultConfig::toArray();

        if (isset($mergedYaml['app']) && is_array($mergedYaml['app'])) {
            $mergedYaml['app']['project_path'] = $pwd.DIRECTORY_SEPARATOR;
        }

        $yaml = Yaml::dump($mergedYaml, 4, 2);
        file_put_contents($newYaml, $yaml);

        ds('Welcome to the LaraDumps!');

        $this->components->info('The <comment>laradumps.yaml</comment> configuration file was published at <comment>'.$pwd.'</comment>');

        if (AppendLaradumpsYamlToGitignore::handle()) {
            $this->components->info('<comment>laradumps.yaml</comment> was added to <comment>.gitignore</comment>');
        }

        $this->components->info('Check out our documentation at <comment>https://laradumps.dev/</comment>');

    }
}
