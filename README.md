<p align="center">
  <img src="./art/logo.png" height="128" alt="" />
</p>
<h1 align="center">LaraDumps</h1>
<div align="center">
  <h4><a href="https://laradumps.dev/get-started/installation.html" target="_blank">Download the App</a></h4>
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
    <a href="https://github.com/laradumps/laradumps/actions">
        <img alt="Tests" src="https://github.com/laradumps/laradumps/workflows/LaraDumps%20Tests/badge.svg" />
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

- [Dump](https://laradumps.dev/debug/usage.html#dump) single or multiple variables at once.
- See your dumped values in a [Table](https://laradumps.dev/debug/usage.html#table), with a built-in search feature.
- Improve your debugging experience using different [screens](https://laradumps.dev/debug/usage.html#screens).
- Watch [SQL Queries](hhttps://laradumps.dev/debug/usage.html#sql-queries).
- Monitor [Laravel Logs](https://laravel.com/docs/10.x/logging).
- Validate [JSON strings](https://laradumps.dev/debug/usage.html#json).
- Verify if a string [contains](https://laradumps.dev/debug/usage.html#contains) a substring.
- View `phpinfo()` configuration.
- List your [Laravel Routes](https://laravel.com/docs/10.x/routing).
- Inspect [Model](https://laravel.com/docs/10.x/eloquent) attributes.
- Learn more in our [Reference Sheet](https://laradumps.dev/debug/reference-sheet.html).

<br/>

### Get Started

#### Requirements

 PHP 8.0+ and Laravel 8.75+

#### Using Laravel
```shell
 composer require laradumps/laradumps --dev
 ```

#### PHP Project
```shell
 composer require laradumps/laradumps-core --dev
 ```

See also: https://laradumps.dev/get-started/release-notes.html#php-package

* Debug your code using `ds()` in the same way you would use Laravel's native functions dump() or dd().

* Run your Laravel application and see the debug dump in LaraDumps App window.

### Example

Here's an example:

```php
// File: routes/web.php

<?php 

Route::get('/', function () {
    ds('Home page accessed!');
    return view('home');
});
```

The Desktop App receives:

<p align="center">
  <img src="./art/light_mode_example.png" height="500" alt="" />
  <img src="./art/dark_mode_example.png" height="500" alt="" />
</p>

### Credits

LaraDumps is a free open-source project, and it was inspired by [Spatie Ray](https://github.com/spatie/ray), check it out!

- Author: [Luan Freitas](https://github.com/luanfreitasdev)

- Logo by [Vitor S. Rodrigues](https://github.com/vs0uz4)
