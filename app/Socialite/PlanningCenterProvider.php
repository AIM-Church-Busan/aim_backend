<?php

namespace App\Socialite;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\PlanningCenter\Provider as BaseProvider;

class PlanningCenterProvider extends BaseProvider
{
    public function getAccessTokenResponse($code)
    {
        if (is_null($code)) {
            $code = request()->query('code');
        }

        try {
            $response = $this->getHttpClient()->post($this->getTokenUrl(), [
                RequestOptions::FORM_PARAMS => [
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri'  => $this->redirectUrl,
                ],
            ]);
            return json_decode((string) $response->getBody(), true);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw $e;
        }
    }
}
