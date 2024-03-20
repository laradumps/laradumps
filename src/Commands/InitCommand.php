<?php

namespace LaraDumps\LaraDumps\Commands;

use Illuminate\Console\Command;
use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'ds:init',
    description: 'Init',
    hidden: false
)]
class InitCommand extends Command
{
    protected $signature = 'ds:init {pwd}';

    protected $description = 'Laradumps Init';

    public function handle(): void
    {
        /** @var string $pwd */
        $pwd = $this->argument('pwd');

        if (Config::exists()) {
            ds('Welcome back to the LaraDumps!');

            $this->components->info('laradumps.yaml has already been published');

            return;
        }

        $defaultYaml = appBasePath() . 'vendor/laradumps/laradumps-core/src/Commands/laradumps-base.yaml';

        $publish = Config::publish(
            pwd: $pwd . DIRECTORY_SEPARATOR,
            filepath: $defaultYaml
        );

        $newYaml = appBasePath() . 'laradumps.yaml';

        if ($publish) {
            /** @var array $yamlFile */
            $yamlFile = Yaml::parseFile(__DIR__ . '/laradumps-base.yaml');
            /** @var array $default */
            $default = Yaml::parseFile($defaultYaml);

            foreach ($default as $key => $values) {
                foreach ($values as $key1 => $value) {
                    $default[$key][$key1] = $value;
                }
            }

            $yamlFile['app']['project_path'] = $pwd . DIRECTORY_SEPARATOR;

            $mergedYaml = array_replace_recursive($default, $yamlFile);

            $yaml = Yaml::dump($mergedYaml);
            file_put_contents($newYaml, $yaml);

            $this->sendMessageToApp();

            $this->components->info('The laradumps.yaml file was published in <comment>' . $pwd . '</comment>');
            $this->components->info('Read the docs: https://laradumps.dev/debug/usage.html');
        };
    }

    private function sendMessageToApp(): void
    {
        ds('Welcome to the LaraDumps!');
    }
}
