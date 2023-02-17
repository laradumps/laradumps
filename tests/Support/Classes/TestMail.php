<?php

namespace LaraDumps\LaraDumps\Tests\Support\Classes;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->to('to@example.com', 'To Test')
            ->from('from@example.com', 'From Test')
            ->subject('An test mail')
            ->html($this->getHTML());
    }

    protected function getHTML(): string
    {
        return <<<HTML
        Hi,<br/><br/>
        This is a <b>test mail</b>
        HTML;
    }
}
