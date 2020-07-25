[![Maintainability](https://api.codeclimate.com/v1/badges/b0f56f63447392a377e5/maintainability)](https://codeclimate.com/github/bezrukov/php-project-lvl2/maintainability)
![](https://github.com/bezrukov/php-project-lvl2/workflows/PHP%20CI/badge.svg)


### Install

```bash
composer require v.bezrukov/gendiff
```

### Examples
#### Pretty format
```bash
gendiff before.json after.json
```
[![asciicast](https://asciinema.org/a/349653.png)](https://asciinema.org/a/349653)

#### Yaml format
```bash
gendiff before.yaml after.yaml
```
[![asciicast](https://asciinema.org/a/349657.png)](https://asciinema.org/a/349657)

#### Recursive structure
```bash
gendiff before.json after.json
```
[![asciicast](https://asciinema.org/a/349654.png)](https://asciinema.org/a/349654)

### Plain format
```bash
gendiff --format plain before.json after.json
```
[![asciicast](https://asciinema.org/a/349655.png)](https://asciinema.org/a/349655)

### Json format
```bash
gendiff --format json before.json after.json
```
[![asciicast](https://asciinema.org/a/349661.png)](https://asciinema.org/a/349661)