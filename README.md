<p align="center">
  <img src="./art/logo.png" height="128" alt="" />
</p>
<h1 align="center">LaraDumps</h1>
<div align="center">
  <br />
  <p align="center">
    <a href="https://github.com/laradumps/app/releases/download/v0.1.0/laradumps-Setup-0.1.0.exe">
      <img src="./art/os/windows.png" height="60" alt="LaraDumps Windows App" />
    </a>
    <a href="https://github.com/laradumps/app/releases/download/v0.1.0/laradumps-0.1.0.dmg">
      <img src="./art/os/macos.png" height="60" alt="LaraDumps MacOS App" />
    </a>
    <a href="https://github.com/laradumps/app/releases/download/v0.1.0/laradumps-0.1.0.AppImage">
      <img src="./art/os/linux.png" height="60" alt="LaraDumps Linux App" />
    </a>
  </p>
  <sub>Available for Windows, Linux and macOS. Click on your OS logo to download the app.</sub>
  <br />
  <br />
  <p>
    <a href="https://laradumps.gitbook.io/laradumps/"> 📚 Documentation </a>
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

LaraDumps is a lightweight app designed to boost your [Laravel](https://larvel.com/) PHP coding and debugging experience.

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
- List your Laravel routes.
- Inspect Model attributes.
- Send your dump to different screens, resulting in a better debugging experience.

Learn more at the [Reference Sheet](https://laradumps.gitbook.io/laradumps/reference).

<br>

### Get Started

#### Requirements

 PHP 8.0+ and Laravel 8.75+

#### Usage

1. Download the [LaraDumps](https://github.com/laradumps/app) App.

2. Install LaraDumps in your project, run `  composer require laradumps/laradumps --dev`.

3. Debug your code using `ds()` instead of Laravel native dd() tool.

4. Run your Laravel application and see the debug result in LaraDumps app window.

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
