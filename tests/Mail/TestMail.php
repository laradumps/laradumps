<?php

namespace LaraDumps\LaraDumps\Tests\Mail;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to('to@example.com', 'To Test')
                ->from('from@example.com', 'From Test')
                ->html($this->getHTML());
    }

    protected function getHTML()
    {
        return <<<HTML
        Hi,<br/><br/>
        This is a <b>test mail</b>
        HTML;
    }
}
