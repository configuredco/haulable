<p align="center">
<a href="https://packagist.org/packages/configured/haulable"><img src="https://img.shields.io/packagist/dt/configured/haulable" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/configured/haulable"><img src="https://img.shields.io/packagist/v/configured/haulable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/configured/haulable"><img src="https://img.shields.io/packagist/l/configured/haulable" alt="License"></a>
</p>


![image](https://user-images.githubusercontent.com/3619398/228083152-5758103f-c27b-4d53-a1d5-c3287ee05949.png)



Haulable is a CLI tool that bundles your existing PHP CLI Phar with [PHP Micro CLI](https://github.com/dixyes/phpmicro). This enables your existing PHP CLI app built with something like [Laravel Zero](https://github.com/laravel-zero/laravel-zero) to be truly portable as the end user will no longer need PHP to be installed to run your application.


### Requirements 

This CLI app only works for MacOS & Linux.


### Installation

To install Haulable, you can use `composer` to install globally, or you can use the Phar directly by downloading the latest build.

```bash
composer global require configured/haulable
```

### Usage

To use Haulable, once installed, you can simply run `./haulable your_cli_app.phar`. Haulable will then ask you for what target system(s) you'd like to bundle your CLI app for.

![image](https://user-images.githubusercontent.com/3619398/228082193-b32031f7-cb9e-441b-a298-3d4f1ea9ca43.png)


## License
Haulable is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
