<?php

namespace App\Actions;

class VerifyDNSAction
{
    const DNS_RECORD_TYPE_IDENTIFER = 'a';
    const DNS_RECORD_KEY_IDENTIFIER = 'p';

    private string $type;
    private string $location;
    private string $key;

    public function __construct(string $type, string $location, string $key)
    {
        $this->type = $type;
        $this->location = $location;
        $this->key = $key;
    }

    public function verify()
    {
        if (!$this->checkKeyIsInLocationDNSRecord($this->type, $this->key, $this->location)) {
            return false;
        }

        return true;
    }

    private function checkKeyIsInLocationDNSRecord(string $type, string $key, string $location) :  bool
    {
        $locationTXTRecords = (new GetDNSTxtRecordsAction($location))->getRecords();

        // type received on API is given as uppercasepoti
        $type = strtolower($type);

        foreach ($locationTXTRecords as $_ => $value) {
            $recordType = data_get($value, self::DNS_RECORD_TYPE_IDENTIFER);
            $recordKey = data_get($value, self::DNS_RECORD_KEY_IDENTIFIER);

            if ($recordType == $type && $recordKey == $key) {
                return true;
            }
        }

        return false;
    }
}

