<?php
/**
 * User: echo
 * Date: 17/2/17
 * Time: 下午12:40
 */

require __DIR__ . '/vendor/autoload.php';

use wgqi1126\ProcessUnique\ProcessUnique;

$pu = new ProcessUnique('process-unique-test');

print_r($pu);

while (true) {
    if ($pu->exists()) {
        echo "process exists, wait\n";
        sleep(5);
        continue;
    } else {
        echo "process not exists, start run\n";
        $pu->save();
        sleep(10);
        exit();
    }
}
