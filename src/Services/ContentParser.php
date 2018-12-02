<?php

namespace App\Services;

class ContentParser
{
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
            $password = $content['password'];
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
                $password = $content['password'];
                $email = $content['email'];
            }
        }

        return [
            'email' => $email,
            'password' => $password,
        ];
    }
}