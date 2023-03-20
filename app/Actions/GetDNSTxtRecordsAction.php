<?php

namespace App\Actions;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class GetDNSTxtRecordsAction
{

    const DNS_LOOKUP_URL = "https://dns.google/resolve?name=[LOCATION]&type=TXT";
    const DNS_LOCATION_FIELD_IDENTIFIER = "[LOCATION]";
    const DNS_VALUE_PREFIX = "openatts";

    private string $location;

    public function __construct(string $location)
    {
        $this->location = $location;
    }

    public function getRecords() : array
    {
        $dnsUrl = str_replace(
            self::DNS_LOCATION_FIELD_IDENTIFIER,
            $this->location,
            self::DNS_LOOKUP_URL,
        );

        $response = Http::get($dnsUrl)->json();

        return Arr::map(
            $response['Answer'],
            function (array $value) {
                $data = str_replace(
                    self::DNS_VALUE_PREFIX,
                    "",
                    $value['data'],
                );
                $data = trim($data);
                $data = explode(" ", $data);

                return array_reduce(
                    $data,
                    function ($carry, $item) {
                        [$key, $value] = explode("=", $item);
                        $value = str_replace(";", "", $value);
                        $carry[$key] = $value;
                        return $carry;
                    },
                    []
                );
            }
        );
    }
}
