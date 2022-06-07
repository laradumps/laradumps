<p align="center">
  <img src="./icon.png" height="128"  alt=""/>
</p>
<h1 align="center">LaraDumps</h1>
<div align="center">
  <br>
  <sub>Available for Windows, Linux and macOS.</sub>
</div>
<br>
<div align="center">
  <p align="center">
        <a href="https://packagist.org/packages/laradumps/laradumps"><img alt="Latest Version" src="https://img.shields.io/static/v1?label=laravel&message=%E2%89%A58.0&color=0078BE&logo=laravel&style=flat-square"></a>
        <a href="https://packagist.org/packages/laradumps/laradumps"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/laradumps/laradumps"></a>
        <a href="https://packagist.org/packages/laradumps/laradumps"><img alt="Latest Version" src="https://img.shields.io/packagist/v/laradumps/laradumps"></a>
        <a href="https://packagist.org/packages/laradumps/laradumps"><img alt="License" src="https://img.shields.io/packagist/l/laradumps/laradumps"></a>
  </p>
</div>
<div align="center">
  <h3> 
    <a href="https://laradumps.gitbook.io/laradumps/">
      📚 Documentation
    </a>
    <span> | </span>
    <a href="https://github.com/laradumps/laradumps#contribution">
      ⌨️ Contribution
    </a>
  </h3>
</div>

# What is LaraDumps?

> LaraDumps is an app designed to boost your Laravel PHP coding and debugging experience. Dumps was inspired by [Spatie Ray](https://github.com/spatie/ray), check it out!.

* This project is free and open source, supports only applications built with Laravel Framework.

## Requirements

* PHP 8.0+
* Laravel 8.75+
* Livewire 2.10+

---

## App

#### Tech Stack

* [Electron](https://www.electronjs.org/)

#### Renderer

* VanillaJS
* [AlpineJS](https://alpinejs.dev/)
* [TailwindCSS](https://tailwindcss.com/)

## Here's an example:

```php
ds('Hello world');

ds(['a' => 1, 'b' => 2])->danger();

ds('multiple', 'arguments', $foo, $bar);

ds()->queriesOn('label');

User::firstWhere('email', 'you@email.com');

ds()->queriesOff();

ds()->die(); 
```

And also with the blade directive:

```blade
<div>
    @foreach($array as $key => $value)
        @ds($key, $value);
    @endforeach
</div>
```

### Credits

- Logo by [Vitor S. Rodrigues](https://twitter.com/V1t0rSOuz4)
