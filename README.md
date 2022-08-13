<p align="center">
  <img src="./art/logo.png" height="128" alt="" />
</p>
<h1 align="center">LaraDumps</h1>
<div align="center">
  <br />
  <p align="center">
    <a href="https://github.com/laradumps/app/releases/download/v1.2/LaraDumps-Setup-1.2.exe">
      <img src="./art/os/windows.png" height="60" alt="LaraDumps Windows App" />
    </a>
    <a href="https://github.com/laradumps/app/releases/download/v1.2/LaraDumps-1.2.dmg">
      <img src="./art/os/macos.png" height="60" alt="LaraDumps MacOS App" />
    </a>
    <a href="https://github.com/laradumps/app/releases/download/v1.2/LaraDumps-1.2.AppImage">
      <img src="./art/os/linux.png" height="60" alt="LaraDumps Linux App" />
    </a>
  </p>
  <h3>Click on your OS logo to download the Desktop App.</h3>
  <sub>Available for Windows, Linux and macOS.</sub>
  <br />
  <br />
  <p>
    <a href="https://laradumps.dev"> 📚 Documentation </a>
  </p>
</div>
 <br/>
<div align="center">
  <p align="center">
    <a href="https://packagist.org/packages/laradumps/laradumps">
      <img alt="Latest Version" src="https://img.shields.io/static/v1?label=laravel&message=%E2%89%A58.0&color=0078BE&logo=laravel&style=flat-square">
    </a>
    <a href="https://packagist.org/packages/laradumps/laradumps">
      <img alt="Total Downloads" src="https://img.shields.io/packagist/dt/laradumps/laradumps">
    </a>
    <a href="https://packagist.org/packages/laradumps/laradumps">
      <img alt="Latest Version" src="https://img.shields.io/packagist/v/laradumps/laradumps">
    </a>
    <a href="https://packagist.org/packages/laradumps/laradumps">
      <img alt="License" src="https://img.shields.io/github/license/laradumps/laradumps">
    </a>
  </p>
</div>

### 👋 Hello Dev,

<br/>

LaraDumps is a friendly app designed to boost your [Laravel](https://larvel.com/) PHP coding and debugging experience.

When using LaraDumps, you can see the result of your debug displayed in a standalone Desktop application.

These are some debug tools available for you:

- Dump single or multiple variables at once.
- Dump values in a table format, with a built-in search feature.
- Monitor SQL Queries.
- View Laravel Logs.
- Validate JSON strings.
- Compare strings with `diff`.
- Verify if a string contains a substring.
- View `phpinfo()` configuration.
- Debug Livewire components.
- Debug Livewire Events.
- List your Laravel routes.
- Inspect Model attributes.
- Send your dump to different screens, resulting in a better debugging experience.

Learn more at the [Reference Sheet](https://laradumps.dev/#/laravel/debug/reference-sheet).

<br>

### Get Started

#### Requirements

 PHP 8.0+ and Laravel 8.75+

#### Usage

1. Download the 🖥️ [LaraDumps](https://github.com/laradumps/app) Desktop App, choose your OS: [Windows](https://github.com/laradumps/app/releases/download/v1.2/LaraDumps-Setup-1.2.exe) | [MacOS](https://github.com/laradumps/app/releases/download/v1.2/LaraDumps-1.2.dmg)
 | [Linux](https://github.com/laradumps/app/releases/download/v1.2/LaraDumps-1.2.AppImage)

2. Install LaraDumps in your Laravel project, run:

```shell
 composer require laradumps/laradumps --dev
 ```

3. Configure LaraDumps, run:

```shell
php artisan ds:init
 ```

4. Debug your code using `ds()` instead of Laravel native dump() or dd() tool.

5. Run your Laravel application and see the debug dump in LaraDumps App window.

Here's an example:

```php
Route::get('/', function () {
    ds('Home page accessed!');
    return view('home');
});
```

### Credits

LaraDumps is a free open-source project, and it was inspired by [Spatie Ray](https://github.com/spatie/ray), check it out!

- Author: [Luan Freitas](https://github.com/luanfreitasdev)

- Logo by [Vitor S. Rodrigues](https://github.com/vs0uz4)
