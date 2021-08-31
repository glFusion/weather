# Changelog - Weather plugin for glFusion

## Version 2.0.2 - 2021-08-31
- Fix bad call to logging function

## Version 2.0.1 - 2021-08-29
- Fix passing location arguments to API classes

## Version 2.0.0 - 2020-09-02
- Remove public index.php interface, only used for API access now.
- Add dvlpupdate.php for tracking develop branch
- Improve class namespacing
- Remove World Weather Online, no longer working
- Fix cache key creation
- Fix fieldset for APIXU config elements

## Version 1.1.1 - 2018-08-14
- Remove glfusion 2 caching, use DB only
- Remove APIXU language strings, use iso_lang in request instead
- Increase connection timeout (for APIXU)
- Fix bad reference to "wind_condition" var in phpblock

## Version 1.1.0 - 2018-08-12
- Use glFusion cache (req. glFusion 2.0.0 or higher)
- Implement Weather namespace and class autoloader
- Add apixu.com weather provider
- Add changes from matrox66 for World Weather Online class
- Extract common weather functions into base_api.class.php

## Version 1.0.3 - 2016-11-06
- Hide large description from display on small viewports
- Remove World Weather Online as preferred provider
- Updates for glFusion 1.5+ and UIkit themes

## Version 0.1.3 -
- Switch from the deprecated Google weather API to World Weather Online.

## Version 0.1.1 - 2011-03-15
- Prefer curl over file_get_contents since it handles other character sets better.
- Clean up the cache with each WEATHER_updateCache() call.

## Version 0.1.0 - 2011-01-16
- Initial public Beta release
