<?php

namespace LaraDumps\LaraDumps\Commands\Concerns;

trait RenderAscii
{
    /**
     * LaraDumps in Ascii Art
     */
    public function renderLogo(): void
    {
        $this->newLine();

        $this->line(<<<EOT
         <fg=yellow>_                     _____                            
        | |                   |  __ \                           
        | |     __ _ _ __ __ _| |  | |_   _ _ __ ___  _ __  ___ 
        | |    / _` | '__/ _` | |  | | | | | '_ ` _ \| '_ \/ __|
        | |___| (_| | | | (_| | |__| | |_| | | | | | | |_) \__ \
        |______\__,_|_|  \__,_|_____/ \__,_|_| |_| |_| .__/|___/
                                                     | |        
                                                     |_|        </>
        EOT);

        $this->newLine(1);
    }
}
