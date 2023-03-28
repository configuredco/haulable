<?php

namespace App\Commands;

use App\Enums\Platform;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use function Termwind\{render}; //@codingStandardsIgnoreStart

class Package extends Command
{
    protected $signature = 'package {phar} {--platform=}';

    protected $description = 'Package PHAR with PHP Micro';

    protected const STORAGE_URL = 'https://haulable.configured.co/';

    protected ProgressBar $progress;

    protected ?Platform $platform;

    public function handle(): void
    {
        if (! File::exists($this->argument('phar'))) {
            $this->error('The path to the PHAR could not be found.');

            exit();
        }

        $this->setupProgress();

        render(view('intro', ['version' => app('git.version')]));

        $this->determinePlatform();

        match ($this->platform) {
            Platform::ALL_PLATFORMS => $this->packageForAllPlatforms(),
            null => $this->error('The specified --platform option is invalid.'),
            default => $this->package($this->platform)
        };
    }

    private function determinePlatform(): void
    {
        if (! $choice = $this->option('platform')) {
            $choice = $this->choice('Create for which platform?', array_column(Platform::cases(), 'value'));
        }

        $this->platform = Platform::tryFrom($choice);
    }

    private function packageForAllPlatforms(): void
    {
        collect(Platform::cases())->skip(1)->each(fn (Platform $platform) => $this->package($platform));
    }

    private function package(Platform $platform, string $phpVersion = null): void
    {
        $sfx = $this->downloadSfx($platform, $phpVersion);

        $dir = getcwd().'/'.str($platform->value)->lower()->replace(['(', ')'], '')->replace(' ', '_')->value;

        $buildName = basename($this->argument('phar'));
        $fileExtension = $platform === Platform::WINDOWS ? '.exe' : '';

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir);
        }

        $result = Process::run("cat {$sfx} {$this->argument('phar')} > {$dir}/{$buildName}{$fileExtension}");

        match (true) {
            $result->successful() => render(
                view('packaging-successful', [
                    'platform' => $platform,
                    'dir' => $dir,
                    'buildName' => $buildName,
                    'fileExtension' => $fileExtension,
                ])
            ),
            default => render(view('error'))
        };
    }

    private function downloadSfx(Platform $platform, string $phpVersion = null): string
    {
        $filename = $this->sfxFilename($platform, $phpVersion);

        if (Storage::exists($filename)) {
            return Storage::path($filename);
        }

        render('💾 Downloading PHP Micro CLI for '.$platform->value);

        $context = stream_context_create([], [
            'notification' => function ($code, $severity, $message, $messageCode, $bytesTransferred, $bytesMax) {
                match ($code) {
                    STREAM_NOTIFY_FILE_SIZE_IS => $this->progress->start($bytesMax),
                    STREAM_NOTIFY_PROGRESS => $this->progress->setProgress($bytesTransferred),
                    default => null
                };
            },
        ]);

        $file = file_get_contents(self::STORAGE_URL.$filename, false, $context);

        Storage::put($filename, $file);

        $this->progress->finish();

        render(PHP_EOL);

        return Storage::path($filename);
    }

    private function sfxFilename(Platform $platform, string $phpVersion = null): ?string
    {
        $phpVersion ??= $this->phpVersion();

        return match ([$platform, $phpVersion]) {
            [Platform::MACOS_INTEL, '8.0'] => 'micro-cli_8.0_macos_intel.sfx',
            [Platform::MACOS_APPLE, '8.0'] => 'micro-cli_8.0_macos_apple.sfx',
            [Platform::LINUX, '8.0'] => 'micro-cli_8.0_linux_x86_64.sfx',
            [Platform::WINDOWS, '8.0'] => 'micro-cli_8.0_windows_x64.sfx',
            [Platform::MACOS_INTEL, '8.1'] => 'micro-cli_8.1_macos_intel.sfx',
            [Platform::MACOS_APPLE, '8.1'] => 'micro-cli_8.1_macos_apple.sfx',
            [Platform::LINUX, '8.1'] => 'micro-cli_8.1_linux_x86_64.sfx',
            [Platform::WINDOWS, '8.1'] => 'micro-cli_8.1_windows_x64.sfx',
            [Platform::MACOS_INTEL, '8.2'] => 'micro-cli_8.2_macos_intel.sfx',
            [Platform::MACOS_APPLE, '8.2'] => 'micro-cli_8.2_macos_apple.sfx',
            [Platform::LINUX, '8.2'] => 'micro-cli_8.2_linux_x86_64.sfx',
            [Platform::WINDOWS, '8.2'] => 'micro-cli_8.2_windows_x64.sfx',
            default => $this->throwInvalidPhpVersion($phpVersion)
        };
    }

    private function phpVersion(): string
    {
        return str(phpversion())->beforeLast('.')->value();
    }

    private function setupProgress(): void
    {
        $this->progress = $this->output->createProgressBar();
        $this->progress->setFormat('[%bar%] %percent%%');
    }

    private function throwInvalidPhpVersion(string $phpVersion): void
    {
        $this->error("The needed PHP Micro version could not be found for your PHP version ({$phpVersion}).");

        exit();
    }
}
