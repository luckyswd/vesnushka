<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Process\Process;

$groups = ['items', 'brand', 'category', 'attr'];

for ($i = 1; $i <= 100; ++$i) {
    echo "=== Run $i / 100 ===\n";

    foreach ($groups as $group) {
        echo "-> Loading group: $group\n";

        $process = new Process(['php', 'bin/console', 'doctrine:fixtures:load', '--group=' . $group, '--append']);
        $process->setTimeout(3600);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            echo "Process failed on group '$group' at iteration $i\n";
            break 2;
        }
    }

    echo "=== Done $i / 100 ===\n\n";
}
