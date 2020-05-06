# Weather plugin for glFusion
Copyright (C) 2011-2020 by Lee Garner  lee@leegarner.com

The Weather plugin allows you to integrate weather informationwith other
plugins such as Locator and Evlist. As of version 2.0.0 there is no longer
a public-facing page for users to look up weather. All access is via API
calls.

The Weather plugin requires either Curl support or `allow_url_fopen` set in php.ini.

## Providers
#### Weatherstack
The free plan only provides for current weather. See https://weatherstack.com
to sign up for a free API key.

#### OpenWeather
OpenWeather provides current and forecast data under the free plan. Visit
https://openweathermap.org/api to sign up for a free API key.

#### Weather Unlocked
OpenWeather provides current and forecast data under the free plan. Visit
https://developer.weatherunlocked.com/ to sign up for a free API key.

## Deprecated Providers
As of version 2.0.0, the following providers have been deprecates as they
are no longer available:
  * Weather Underground
  * World Weather Online
  * APIXU (now Weatherstack)

## Administration
There is no administration interface provided other than a link to purge the cache.
