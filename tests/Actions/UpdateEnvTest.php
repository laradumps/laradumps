<?php

use Illuminate\Support\Facades\File;
use LaraDumps\LaraDumps\Actions\UpdateEnv;

beforeEach(function () {
    $this->filename = md5(uniqid(rand(), true));
    $this->filePath = base_path($this->filename);
});

afterEach(function () {
    File::delete($this->filePath);
});

it('can update the .env file', function () {
    File::put($this->filePath, 'NAME="Luan"' . PHP_EOL . 'PRICE=10' . PHP_EOL . 'ACTIVE=true');

    UpdateEnv::handle('name', 'Dan', $this->filename);
    UpdateEnv::handle('price', 20, $this->filename);
    UpdateEnv::handle('active', false, $this->filename);

    expect(File::get($this->filePath))
        ->toContain('NAME="Dan"')
        ->toContain('PRICE=20')
        ->toContain('ACTIVE=false');
});
