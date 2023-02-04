# How to contribute with LaraDumps

Thank you for your interesting in contributing to LaraDumps.

If you have any questions, do not hesitate to reach the community in the repository [Discussions](https://github.com/laradumps/laradumps/discussions) tab.

<br/>

👉 **Important:**  Your feature or bug fix might also require changes in the [LaraDumps App](https://github.com/laradumps/app).

<br/>

## Get Started

1 .**Fork**

```shell
git clone https://github.com/laradumps/laradumps.git && cd laradumps
```

Install all dependencies with composer and NPM.

```shell
composer install
```

Update the composer dependencies.

```Shell
composer update
```

<br/>

2. **Create a new branch**

Create a new branch specifying `feature`, `fix`, `enhancement`.

```shell
git checkout -b feature/my-new-feature
```

<br/>

3. **Code and check your work**


Write your code and, when you are done, run the following checks:

Run the CS Fix:

```Shell
composer fix
```

Run tests and static analysis:

```Shell
composer verify
```

<br/>

4. **Tests**

Including tests is not mandatory, but if you can write tests, please consider doing it.

<br/>


5. **Commit**

Please send clean and descriptive commits.

<br/>


6. **Pull Request**

Open a Pull Request (PR) detailing your changes and motivations. Please make only one change per Pull Request.

If you never wrote a PR before, see this excellent [example](https://github.com/Power-Components/livewire-powergrid/pull/149) by [@vs0uz4](https://github.com/vs0uz4) for inspiration.

<br/>

💓  Thank you for contributing!
