<?php

use Illuminate\Support\Facades\File;
use LaraDumps\LaraDumps\Actions\WriteEnv;

it('does not accept invalid keys', function (string $key) {
    WriteEnv::handle([$key => true], $this->tempFile);
})
->throws(Exception::class)
->with(['', 'DS-APP', 'DS_APP!', 'DS APP']);

it('properly adds a key to a .env')
    ->expect(fn () => envFile())->not->toContain('DS_FOO')
    ->tap(fn () => WriteEnv::handle(['DS_FOO' => 'bar', 'DS_BAR' => true, 'DS_BAZ' => 90125, ], $this->tempFile))
    ->expect(fn () => envFile())
    ->toContain("DS_FOO=\"bar\"\nDS_BAR=true\nDS_BAZ=90125")
    ->toContain('DS_SEND_JOBS');

it('properly updates an .env key')
    ->tap(fn () => WriteEnv::handle(['DS_APP_HOST' => 'server.demo', 'DS_SEND_JOBS' => ''], $this->tempFile))
    ->expect(fn () => envFile())
    ->toContain('DS_APP_HOST="server.demo"')
    ->toContain("DS_SEND_JOBS=\nDS_SEND_COMMANDS=true")
    ->not->toContain('127.0.0.1');

beforeEach(function () {
    $this->tempFile = str_replace('/', DIRECTORY_SEPARATOR, sys_get_temp_dir() . '/.env');

    $this->envFile  = str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/../../Fixtures/ds_env');

    File::copy($this->envFile, $this->tempFile);
});

afterEach(function () {
    if (File::exists($this->tempFile)) {
        File::delete($this->tempFile);
    }
});

function envFile(): string
{
    return  str_replace(["\r", "\t"], '', strval(file_get_contents(test()->tempFile)));
}
