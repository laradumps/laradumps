<?php

namespace LaraDumps\LaraDumps\Commands;

use Illuminate\Console\Command;
use LaraDumps\LaraDumps\Actions\GitDirtyFiles;
use LaraDumps\LaraDumps\Support\IdeHandle;
use Symfony\Component\Finder\Finder;

use function Termwind\{render, renderUsing};

class CheckCommand extends Command
{
    protected $signature = 'ds:check {--dirty}';

    protected $description = 'Check if you forgot any ds() in your files';

    public function handle(): int
    {
        $dirtyFiles = [];

        if (!empty($this->option('dirty'))) {
            $dirtyFiles = GitDirtyFiles::run();
        }

        /** @var array<string>|string $directories */
        $directories = config('laradumps.ci_check.directories');

        $ignoreLineWhenContainsText = config('laradumps.ci_check.ignore_line_when_contains_text');

        $textToSearch = config('laradumps.ci_check.text_to_search');

        renderUsing($this->output);

        $matches = [];

        $finder = new Finder();

        $finder->files()->in($directories);

        $progressBar = $this->output->createProgressBar(count($dirtyFiles) ?: $finder->count());

        $this->output->writeln('');

        foreach ($finder as $file) {
            if (filled($dirtyFiles) && !in_array($file->getRealPath(), $dirtyFiles)) {
                continue;
            }

            $progressBar->advance();

            /** @var string[] $contents */
            $contents = file($file->getRealPath());

            foreach ($contents as $line => $lineContent) {
                $contains  = false;
                $ignore    = false;

                /** @var string[] $ignoreLineWhenContainsText */
                foreach ($ignoreLineWhenContainsText as $text) {
                    if (strpos(strtolower($lineContent), strtolower($text))) {
                        $ignore = true;

                        break;
                    }
                }

                /** @var string[] $textToSearch */
                foreach ($textToSearch as $search) {
                    $search = ' ' . ltrim($search);// mantaining compatiblity with V1.0.2;

                    if (strpos($lineContent, $search)
                        || strpos($lineContent, '@' . ltrim($search))
                        || strpos($lineContent, '//' . ltrim($search))
                        || strpos($lineContent, '->' . ltrim($search))
                    ) {
                        $contains = true;

                        break;
                    }
                }

                if ($contains && !$ignore) {
                    $matches[] = $this->saveContent($file, $lineContent, $line);
                }
            }
        }

        $this->output->writeln('');
        $this->output->writeln('');

        foreach ($matches as $iterator => $content) {
            $this->output->writeln(
                ' ' . ($iterator + 1)
                . '<href=' . $content['link'] . '>  '
                . $content['realPath']
                . ':'
                . $content['line']
                . '</>'
            );

            render(
                view('laradumps::output', [
                    'line'    => $content['line'],
                    'content' => $content['content'],
                ])
            );
        }

        $progressBar->finish();

        $this->output->writeln('');

        if (($total = count($matches)) > 0) {
            render(
                view('laradumps::summary', [
                    'error'      => true,
                    'total'      => $total,
                    'totalFiles' => collect($matches)->unique('realPath')->count(),
                ])
            );

            return Command::FAILURE;
        }

        render(
            view('laradumps::summary', [
                'error' => false,
                'total' => 0,
            ])
        );

        return Command::SUCCESS;
    }

    private function saveContent(\SplFileInfo $file, string $lineContent, int $line): array
    {
        /** @var array $fileContents */
        $fileContents = file($file->getRealPath());

        $partialContent = $fileContents[$line - 2]  ?? '';
        $partialContent .= $fileContents[$line - 1] ?? '';

        $partialContent .= $lineContent;
        $partialContent .= $fileContents[$line + 1] ?? '';

        return [
            'line'     => $line + 1,
            'file'     => str_replace(base_path() . '/', '', $file->getRealPath()),
            'realPath' => 'file:///' . $file->getRealPath(),
            'link'     => IdeHandle::makeFileHandler($file->getRealPath(), $line + 1),
            'content'  => $partialContent,
        ];
    }
}
