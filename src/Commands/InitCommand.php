<?php

namespace LaraDumps\LaraDumps\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
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

        $defaultYaml = appBasePath().'vendor/laradumps/laradumps-core/src/Commands/laradumps-base.yaml';

        $publish = Config::publish(
            pwd: $pwd.DIRECTORY_SEPARATOR,
            filepath: $defaultYaml
        );

        if (! $publish) {
            $this->components->error('Failed to publish the laradumps.yaml file. Please check your permissions or the provided path.');

            return;
        }

        $newYaml = appBasePath().'laradumps.yaml';

        /** @var array $yamlFile */
        $yamlFile = Yaml::parseFile(__DIR__.'/laradumps-base.yaml');
        /** @var array $default */
        $default = Yaml::parseFile($defaultYaml);

        foreach ($default as $key => $values) {
            /**
             * @var string $key1
             * @var array $values
             */
            foreach ($values as $key1 => $value) {
                $default[$key][$key1] = $value;
            }
        }

        $yamlFile['app']['project_path'] = $pwd.DIRECTORY_SEPARATOR;

        $mergedYaml = array_replace_recursive($default, $yamlFile);

        $yaml = Yaml::dump($mergedYaml);
        file_put_contents($newYaml, $yaml);

        ds('Welcome to the LaraDumps!');

        $this->components->info('The laradumps.yaml file was published in <comment>'.$pwd.'</comment>');
        $this->components->info('Read the docs: https://laradumps.dev/debug/usage.html');

        Process::run('echo "laradumps.yaml" >> .gitignore');
    }
}
