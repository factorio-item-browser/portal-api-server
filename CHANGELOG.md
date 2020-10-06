# Changelog

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
