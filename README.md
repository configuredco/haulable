<p align="center">
<a href="https://packagist.org/packages/configured/haulable"><img src="https://img.shields.io/packagist/dt/configured/haulable" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/configured/haulable"><img src="https://img.shields.io/packagist/v/configured/haulable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/configured/haulable"><img src="https://img.shields.io/packagist/l/configured/haulable" alt="License"></a>
</p>


![image](https://user-images.githubusercontent.com/3619398/228083152-5758103f-c27b-4d53-a1d5-c3287ee05949.png)


Haulable is a CLI tool that packages your existing PHP CLI Phar with [PHP Micro CLI](https://github.com/dixyes/phpmicro) for MacOS (Apple/Intel), Linux, and Windows. This enables your existing PHP CLI app built with something like [Laravel Zero](https://github.com/laravel-zero/laravel-zero) to be truly portable as the end user will no longer need PHP to be installed to run your application.


### Requirements 

* Haulable currently only runs on MacOS/Linux; however, it will package your CLI for Windows. 
* Your CLI application must be built using PHP8.0+

### Installation

To install Haulable, you can use `composer` to install globally, or you can use the Phar directly by downloading the latest build.

```bash
composer global require configured/haulable
```

### Usage

To use Haulable, once installed, you can simply run `haulable your_cli_app.phar`. Haulable will then ask you for what target system(s) you'd like to package your CLI app for.

![image](https://user-images.githubusercontent.com/3619398/228088979-4d0e06ab-20c3-4a61-a238-9122735db086.png)

#### Options
Haulable accepts options and arguments to make it easier to use in CI pipelines

##### Platform

An option can be one of the following:
* All Platforms
* MacOS (Intel)
* MacOS (Apple)
* Linux (x86_64)
* Linux (aarch64)
* Windows (x64)

```bash
haulable your_cli_app.phar --platform="<option>"
```

## License
Haulable is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
