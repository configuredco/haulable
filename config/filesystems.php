<?php

return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => getenv('HOME').'/.haulable',
        ],
    ],
];
