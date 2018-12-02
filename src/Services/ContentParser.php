<?php

namespace App\Services;

/**
 * Parse content that is send in body request. Some time is url (email=name@example.com&password=1234) or json encoded.
 *
 * Class ContentParser
 * @package App\Services
 */
class ContentParser
{
    /**
     * Return an array with the keys email and password. The password is encoded here with bcrypt.
     *
     * @param $string
     * @return array $response
     */
    public function parse($string)
    {
        $password = '';
        $email = '';

        // parse string if is json
        $content = json_decode($string, true);
        if (is_array($content) &&
            array_key_exists('email', $content) &&
            array_key_exists('password', $content)
        ) {
            $password = md5($content['password']);
            $email = $content['email'];
        }

        // parse string if is url params like
        // the nelmio api doc, in this moment do not allow json body and send in body url encoded
        if (empty($user) && empty($email)) {
            parse_str(urldecode($string), $content);
            if (is_array($content) &&
                array_key_exists('email', $content) &&
                array_key_exists('password', $content)
            ) {
                $password = md5($content['password']);
                $email = $content['email'];
            }
        }

        return [
            'email' => $email,
            'password' => $password,
        ];
    }
}
