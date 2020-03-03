# Changelog
All notable changes to this project will be documented in this file.

## [1.1.0] - 2020-03-03

### Fixed
- composer json for an insecure version of symfony/http-foundation

## [1.0.9] - 2020-03-03

### Fixed
- bundle for command line calls (null-checks for the request object)

## [1.0.8] - 2019-02-22

### Fixed 
- typo in path to service yaml

## [1.0.7] - 2019-02-20

### Changed
- moved service config loading to Plugin class

### Fixed
- Added class alias for `@huh.request` service to fix symfony 4 compatibility

## [1.0.6] - 2018-10-23

### Fixed
- composer dependencies for contao 4.6

## [1.0.5] - 2018-10-10

### Fixed
- `README` technical instruction section

## [1.0.4] - 2018-05-24

### Fixed
- check if current request parameters are null

## [1.0.3] - 2018-03-23

### Fixed
- $request in construct

## [1.0.2] - 2018-03-22

### Fixed
- fixed call of `parent::__construct` with empty properties

## [1.0.1] - 2018-03-12

### Fixed
- removed `heimrichhannot/contao-utils-bundle` circular dependency
