# Changelog

## Unreleased

N/A.

## 0.4.0 (2024-03-12)

### Changed

- Remove the `args` command and allow for arbitrary arguments - e.g. `./bin/console install -- --no-dev` to run `composer install` with the `--no-dev` option.

## 0.3.0 (2024-02-25)

### Added

- Add `package-install` command to add a new package.
- Add initial JavaScript/TypeScript/Fractal support to `versa install` and `versa run`.
- Add a Symfony project type.
- Automatically use PHPUnit or ParaTest based on `require-dev` dependencies.
- Automatically find the PHP project type (i.e. Drupal or Sculpin) based on its `composer.json` dependencies.
- Add `versa build` to build a Sculpin project.
- Add `--extra-args` to pass extra arguments to the underlying command.

### Changed

- Rename `--extra-args` to `--args`.

### Fixed

- Support multiple extra args with spaces, e.g. `versa test --testdox --filter foo`.
- Prevent timeout errors with `versa run`.

## 0.2.0

### Added

- Run `docker compose up` if there is a `docker-compose.yaml` file when running `versa run`.
- Add `--working-dir` to run versa in a different directory.
- Add `-t` as a shortcut for `--type`.

## 0.1.0

### Added

- Add `install` for all projects based on the assumption it's a PHP project.
- Add `test` for all projects based on the assumption it runs PHPUnit.
- Add `run` for Sculpin projects.
