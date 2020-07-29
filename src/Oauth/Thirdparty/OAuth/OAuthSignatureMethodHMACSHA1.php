<?php


namespace Cidaas\OauthConnect\Oauth\Thirdparty\OAuth;

/**
 * Class OAuthSignatureMethodHMACSHA1
 *
 * @package Cidaas\OauthConnect\Oauth\Thirdparty\OAuth
 */
class OAuthSignatureMethodHMACSHA1 extends OAuthSignatureMethod
{
    /**
     * @return string
     */
    public function get_name()
    {
        return "HMAC-SHA1";
    }

    /**
     * @param $request
     * @param $consumer
     * @param $token
     *
     * @return string
     */
    public function build_signature($request, $consumer, $token)
    {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;

        $key_parts = array( $consumer->secret, $token ? $token->secret : '' );

        $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);
        
        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
}
