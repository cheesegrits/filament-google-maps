<?php
return [
	/*
	 | Your Google Maps API key, usually set in .env (but see 'keys' section below).
	 */

    'key' => env('GOOGLE_MAPS_API_KEY'),

	/*
	 | If you need to use both a browser key (restricted by HTTP Referrer) for use in the Javascript API on the
	 | front end, and a server key (restricted by IP address) for server side API calls, you will need to set those
	 | keys here (or preferably set the appropriate env keys)
	 */

	'keys' => [
		'web_key' => env('FILAMENT_GOOGLE_MAPS_WEB_API_KEY', env('GOOGLE_MAPS_API_KEY')),
		'server_key' => env('FILAMENT_GOOGLE_MAPS_SERVER_API_KEY', env('GOOGLE_MAPS_API_KEY')),
	],

	/*
	 | Rate limit for API calls, although you REALLY should also set usage quota limits in your Google Console
	 */

	'rate-limit' => env('FILAMENT_GOOGLE_MAPS_RATE_LIMIT', 150),

	/*
	 | Log channel to use, default is 'null' (no logging), set to your desired channel from logging.php if you want
	 | logs.  Typically only useful for debugging, or if youw ant to keep track of a scheduled geocoding task.
	 */
	'log' => [
		'channel' => env('FILAMENT_GOOGLE_MAPS_LOG_CHANNEL', 'null'),
	],

	/*
	 | Cache store and duration (in seconds) to use for API results.  Specify store as null to use the default from
	 | your cache.php config, false will disable caching (STRONGLY discouraged, unless you want a big Google
	 | API bill!).  For heavy usage, we suggest using a dedicated redis store.  Max cache duration permitted by
	 | Google is 30 days.
	 */

	'cache' => [
		'duration' => env('FILAMENT_GOOGLE_MAPS_CACHE_DURATION_SECONDS', 60 * 60 * 24 * 30),
		'store' => env('FILAMENT_GOOGLE_MAPS_CACHE_STORE', null),
	]
];
