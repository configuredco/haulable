<?php

namespace App\Commands;

use App\Enums\Platform;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use function Termwind\{render}; //@codingStandardsIgnoreLine

class Package extends Command
{
    protected $signature = 'package {phar}';

    protected $description = 'Package PHAR with PHP Micro';

    protected const STORAGE_URL = 'https://haulable.configured.co/';

    protected ProgressBar $progress;

    public function handle(): void
    {
        if (! File::exists($this->argument('phar'))) {
            $this->error('The path to the PHAR could not be found.');

            exit();
        }

        $this->setupProgress();
        $this->displayIntro();

        $choice = $this->choice('Create for which platform?', array_column(Platform::cases(), 'value'));

        match (Platform::from($choice)) {
            Platform::ALL_PLATFORMS => $this->packageForAllPlatforms(),
            default => $this->package(Platform::from($choice))
        };
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
            $result->successful() => render(<<<EOT
                <div class="space-y-1 mb-1">
                    <div>
                        📦 <span class="bg-green-300 text-black px-1">Packaging for {$platform->value} successful.</span>
                    </div>
                    <div>
                        ✅ <a href="{$dir}/{$buildName}{$fileExtension}">{$dir}/{$buildName}{$fileExtension}</a>
                    </div>
                </div>
                <br>
            EOT),
            default => render('😖 <span class="bg-red-400 text-white px-1">Something unexpected went wrong.</span>')
        };
    }

    private function packageForAllPlatforms(): void
    {
        collect(Platform::cases())->skip(1)->each(fn (Platform $platform) => $this->package($platform));
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

    private function setupProgress(): void
    {
        $this->progress = $this->output->createProgressBar();
        $this->progress->setFormat('[%bar%] %percent%%');
    }

    private function phpVersion(): string
    {
        return str(phpversion())->beforeLast('.')->value();
    }

    private function throwInvalidPhpVersion(string $phpVersion): void
    {
        $this->error("The needed PHP Micro version could not be found for your PHP version ({$phpVersion}).");

        exit();
    }

    private function displayIntro(): void
    {
        $version = app('git.version');
        //@codingStandardsIgnoreStart
        render(<<<EOT
        <div class="ml-2">
            <div>
                &nbsp;_&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_&nbsp;&nbsp;&nbsp;&nbsp;_&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                |&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;|&nbsp;&nbsp;|&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                |&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;__,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;|&nbsp;&nbsp;__,&nbsp;|&nbsp;|&nbsp;&nbsp;|&nbsp;|&nbsp;&nbsp;_&nbsp;&nbsp;<br>
                |/&nbsp;\&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;|&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;|/&nbsp;&nbsp;/&nbsp;&nbsp;|&nbsp;|/&nbsp;\_|/&nbsp;&nbsp;|/&nbsp;&nbsp;<br>
                |&nbsp;&nbsp;&nbsp;|_/\_/|_/&nbsp;\_/|_/|__/\_/|_/\_/&nbsp;|__/|__/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </div>
            <div class="px-1 mt-1 bg-green-300 text-black">by ⚙️&nbsp;&nbsp;Configured</div>
            <div class="px-1 mt-1 bg-blue-300 text-black">v{$version}</div>
            <em class="ml-1">
                Create portable PHP CLI applications w/ PHP Micro
            </em>
        </div>
    EOT);
        //@codingStandardsIgnoreEnd
    }
}
