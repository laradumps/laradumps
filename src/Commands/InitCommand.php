<?php

namespace LaraDumps\LaraDumps\Commands;

use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'ds:init',
    description: 'Init',
    hidden: false
)]
class InitCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('pwd', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (Config::exists()) {
            ds('Welcome back to the LaraDumps!');

            $output->writeln('');
            $output->writeln('  ✅  <info><comment>laradumps.yaml</comment> has already been published</info>');
            $output->writeln('');

            return Command::SUCCESS;
        }

        if (is_null($input->getArgument('pwd'))) {
            $output->writeln('Please, run again with the parameter $(pwd): <comment>php artisan ds:init $(pwd)</comment>');
        }

        $defaultYaml = appBasePath() . 'vendor/laradumps/laradumps-core/src/Actions/laradumps-base.yaml';

        $publish = Config::publish(
            pwd: $input->getArgument('pwd') . DIRECTORY_SEPARATOR,
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

            $yamlFile['app']['project_path'] = $input->getArgument('pwd') . DIRECTORY_SEPARATOR;

            $mergedYaml = array_replace_recursive($default, $yamlFile);

            $yaml = Yaml::dump($mergedYaml);
            file_put_contents($newYaml, $yaml);

            $output->writeln('');
            $output->writeln('  ✅  <info>LaraDumps has been successfully configured!</info>');
            $output->writeln('');
            $output->writeln('  ✏️ <info>A file with the settings was created in the root of your project: </info>');
            $output->writeln('');

            $this->sendMessageToApp();
        }

        return Command::SUCCESS;
    }

    private function sendMessageToApp(): void
    {
        ds('Welcome to the LaraDumps!');
    }
}
