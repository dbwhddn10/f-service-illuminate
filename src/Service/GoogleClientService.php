<?php

namespace Illuminate\Extend\Service;

use Carbon\Carbon;
use Google_Client;
use Illuminate\Extend\Service;

class GoogleClientService extends Service
{
    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [];
    }

    public static function getArrLoaders()
    {
        return [
            'result' => ['token', 'credential', function ($token, $credential) {

                $client     = new Google_Client;
                $created    = (int) $token['created'];
                $expiresIn  = (int) $token['expires_in'];
                $now        = (int) Carbon::now('UTC')->timestamp;

                $client->setAccessToken($token);
                $client->setAuthConfig($credential);

                if ( $now > $created + $expiresIn - 60 )
                {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $model->access_token = json_encode($client->getAccessToken());
                    $model->save();
                }

                return $client;
            }],
        ];
    }

    public static function getArrPromiseLists()
    {
        return [];
    }

    public static function getArrRuleLists()
    {
        return [];
    }

    public static function getArrTraits()
    {
        return [];
    }
}
