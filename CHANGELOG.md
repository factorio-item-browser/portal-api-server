# Changelog

## 1.5.2 - 2021-09-05

### Added

- `combinationHash` to the setting data to be able to recognize version changes of the combinations.

## 1.5.1 - 2021-05-24

### Changed

- PHP version from 7.4 to 8.0.

## 1.5.0 - 2021-05-11

### Changed

- Refactored all setting related endpoints, including adding new ones for validating a setting and for requesting the 
  mods of a setting.
- Status code of unknown endpoints to 400 (was 404).
- Endpoint `/style/icons` now expecting the CSS selector (with placeholders) to use for the generated stylesheet.

### Fixed

- `/init` failing if a combination is not yet known to the api server.

## 1.4.1 - 2021-02-19

### Fixed

- Temporary settings could not be saved permanently.

## 1.4.0 - 2021-02-18

### Changed

- Replaced combination-related endpoints of the API with their counterparts in the new Combination API.
- Updated to version 3.0 of the data API.

### Removed

- No longer needed `authorizationKey` from the Setting database table.

## 1.3.0 - 2020-10-06

### Added

- Endpoint `/items` for retrieving a paginated list of all items.

## 1.2.0 - 2020-09-27

### Added

- `Combination-Id` to the header of all requests.
- Creation of temporary setting in `/init` requests if combination is not in one of the current users settings.
- Existing setting to response of `/setting/status` endpoint.

### Changed

- Endpoint `/session/init` to `/init`.
- Response objects now containing `combinationId` of settings instead of their internal id. All setting endpoints now
  expect the combination ids of the settings instead of the internal id.

## 1.0.1 - 2020-06-17

### Added

- Field `scriptVersion` to response of `session/init` to signal the frontend that the scripts changed, and a page reload
  may be necessary.

## 1.0.0 - 2020-05-22

- Initial release of the portal api server project.
