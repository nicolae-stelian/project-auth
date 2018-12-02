<?php

namespace App\Services;


use App\Entity\User;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserSerializer
{
    public function toJsonString(User $user)
    {
        // create normalizer and ignore password field
        $normalize = new ObjectNormalizer();
        $normalize->setIgnoredAttributes(["password", "active"]);
        // create serializer for convert $user object to json.
        $serializer = new Serializer([$normalize], [new JsonEncoder()]);
        // convert $user to json string (data)

        return $serializer->serialize($user, 'json');
    }
}