<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Process\Process;

for ($i = 1; $i <= 50; ++$i) {
    echo "=== Run $i / 20 ===\n";

    $process = new Process(['php', 'bin/console', 'doctrine:fixtures:load', '--group=items', '--append']);
    $process->setTimeout(3600); // 1 час таймаут на всякий случай

    $process->run(function ($type, $buffer) {
        echo $buffer;
    });

    if (!$process->isSuccessful()) {
        echo "Process failed on iteration $i\n";
        break;
    }

    echo "=== Done $i / 20 ===\n\n";
}
