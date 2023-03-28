<?php

namespace App\Enums;

enum Platform: string
{
    case ALL_PLATFORMS = 'All';
    case MACOS_INTEL = 'MacOS (Intel)';
    case MACOS_APPLE = 'MacOS (Apple)';
    case LINUX = 'Linux (x86_64)';
    case WINDOWS = 'Windows (x64)';
}
