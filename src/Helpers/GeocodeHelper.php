<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GeocodeHelper
{
    public static function batchGeocode(Model $model, $lat, $lng, $fields, $rateLimit = 300, $sleepTime = 10, $verbose = false): void
    {
        $url           = 'https://maps.googleapis.com/maps/api/geocode/json';
        $offset        = 0;
        $go            = true;
        $processed     = 0;
        $startTime     = time();
        $ratePerSecond = (int) $rateLimit / 60;

        $table  = $model->getTable();
        $fields = array_map(fn($field) => trim($field), explode(',', $fields));
        $joins  = [];

        foreach ($fields as $field)
        {
            $parts = explode('.', $field);

            if (count($parts) === 2)
            {
                $joins[$field] = [
                    'table' => $parts[0],
                    'field' => $parts[1],
                ];
            }
        }

        $records = $model::where(fn($query) => $query->where([$lat => 0])->orWhereNull($lat))
            ->get();

        $addresses = [];

        foreach ($records as $record)
        {
            $address = [];

            foreach ($fields as $field)
            {
                if (array_key_exists($field, $joins))
                {
                    $address[] = $record->{$joins[$field]['table']}?->{$joins[$field]['field']};
                }
                else
                {
                    $address[] = $record->{$field};
                }
            }

            $addresses[] = implode(',', $address);
        }

        $client = new Client();


//        while ($go) {
//            $result = $client->request('GET', $url,
//                ['query' => [
//                    'origin' => 'Disneyland',
//                    'destination' => 'Universal+Studios+Hollywood',
//                    'key' => config('filament-google-maps.google_map_key')
//                ]
//                ]);
//
////            curl_setopt($ch, CURLOPT_URL, $url . $offset);
////            $output = curl_exec($ch);
//
//            if ($output === false)
//            {
//                $err = curl_error($ch);
//                Worker::log(
//                    'fabrik.cron.irwin.error',
//                    sprintf(
//                        "IRWIN: curl error, exiting: %s",
//                        $err
//                    )
//                );
//
//                $go = false;
//            }
//            else
//            {
//                $json = json_decode($output);
//
//                if (property_exists($json, 'features'))
//                {
//                    foreach ($json->features as $feature)
//                    {
//                        self::upsertIrwin($feature->attributes);
//                        $processed++;
//                    }
//
//                    $offset  += count($json->features);
//                    $seconds = time() - $startTime;
//
//                    if ($verbose)
//                    {
//                        Worker::log(
//                            'fabrik.cron.irwin.info',
//                            sprintf(
//                                "IRWIN: processed %s records, average records per second: %s",
//                                count($json->features),
//                                $offset / $seconds
//                            )
//                        );
//                    }
//
//                    if (count($json->features) < $recordCount)
//                    {
//                        $go = false;
//                        if ($verbose) {
//                            Worker::log(
//                                'fabrik.cron.irwin.info',
//                                sprintf(
//                                    "IRWIN: Normal exit. Asked for %s, got %s. Assuming done.",
//                                    $recordCount, count($json->features)
//                                )
//                            );
//                        }
//                    }
//                    else
//                    {
//                        if ($offset / $seconds > $ratePerSecond)
//                        {
//                            /**
//                             * We're fetching at > rate limit, so sleep for configured number of seconds
//                             */
//                            Worker::log(
//                                'fabrik.cron.irwin.info',
//                                sprintf(
//                                    "IRWIN: exceeded rate limit of %s per second, sleeping for %s seconds",
//                                    $ratePerSecond,
//                                    $sleepTime
//                                )
//                            );
//                            sleep($sleepTime);
//                        }
//                    }
//                }
//                else
//                {
//                    /**
//                     * if error 429, we got rate limited and have to sleep for 60 seconds
//                     */
//                    if (isset($json->error) && (int)$json->error->code === 429)
//                    {
//                        Worker::log(
//                            'fabrik.cron.irwin.info',
//                            sprintf(
//                                "IRWIN: rate limited by server (rateLimit: %s, sleepTime: %s) sleeping for 60 seconds: %s",
//                                $rateLimit,
//                                $sleepTime,
//                                json_encode($json->error)
//                            )
//                        );
//                        sleep(60);
//                    }
//                    else if (isset($json->error))
//                    {
//                        Worker::log(
//                            'fabrik.cron.irwin.error',
//                            sprintf(
//                                "IRWIN: error code %s, exiting: %s",
//                                $json->error->code,
//                                json_encode($json->error)
//                            )
//                        );
//
//                        $go = false;
//                    }
//                    else
//                    {
//                        Worker::log(
//                            'fabrik.cron.irwin.error',
//                            "IRWIN: no features but no error code, exiting"
//                        );
//
//                        $go = false;
//                    }
//                }
//            }
//        }
//
//        curl_close($ch);

    }

}
