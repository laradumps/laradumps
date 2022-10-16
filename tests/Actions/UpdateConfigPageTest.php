<?php

it('updates the config via page', function () {
    $this->artisan('config:clear');

    $this->artisan('ds:init --no-interaction --host=5.6.7.8 --send_queries=false --send_logs=false --send_livewire=false --livewire_events=false --livewire_dispatch=false --livewire_validation=false --livewire_autoclear=false --auto_invoke=false  --ide=vscode');

    $this->post(
        route('laradumps.store'),
        [
            'DS_APP_HOST'                        => '127.0.0.1',
            'DS_AUTO_INVOKE_APP'                 => '1',
            'DS_SEND_QUERIES'                    => '1',
            'DS_SEND_LOGS'                       => '1',
            'DS_SEND_LIVEWIRE_COMPONENTS'        => '1',
            'DS_LIVEWIRE_EVENTS'                 => '1',
            'DS_LIVEWIRE_DISPATCH'               => '1',
            'DS_SEND_LIVEWIRE_FAILED_VALIDATION' => '1',
            'DS_AUTO_CLEAR_ON_PAGE_RELOAD'       => '1',
            'DS_PREFERRED_IDE'                   => 'atom',
        ]
    )->assertStatus(302)
      ->assertRedirect('/laradumps')
      ->assertSessionHas('success', 'Configuration updated successfully!');

    expect(config('laradumps.host'))->toBe('127.0.0.1');
    expect(config('laradumps.send_queries'))->toBeTrue();
    expect(config('laradumps.send_log_applications'))->toBeTrue();
    expect(config('laradumps.send_livewire_components'))->toBeTrue();
    expect(config('laradumps.send_livewire_events'))->toBeTrue();
    expect(config('laradumps.send_livewire_dispatch'))->toBeTrue();
    expect(config('laradumps.send_livewire_failed_validation.enabled'))->toBeTrue();
    expect(config('laradumps.auto_clear_on_page_reload'))->toBeTrue();
    expect(config('laradumps.auto_invoke_app'))->toBeTrue();
    expect(config('laradumps.preferred_ide'))->toBe('atom');
});
