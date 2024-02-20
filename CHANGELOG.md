# Changelog

## Unreleased

### Added

* Add `versa build` to build a Sculpin project.
* Add `--extra-args` to pass extra arguments to the underlying command.

### Fixed

* Prevent timeout errors with `versa run`.

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
