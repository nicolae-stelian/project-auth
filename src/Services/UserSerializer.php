<?php

namespace App\Services;

use App\Entity\User;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * This class serialize the App\Entity\User.
 *
 * Class UserSerializer
 * @package App\Services
 */
class UserSerializer
{
    public function toJsonString(User $user)
    {
        // create normalizer and ignore password field
        $normalize = new ObjectNormalizer(
            null,
            null,
            null,
            null,
            null,
            null,
            ["ignored_attributes" => ["password", "active"]]
        );

        // create serializer for convert $user object to json.
        $serializer = new Serializer([$normalize], [new JsonEncoder()]);
        // convert $user to json string (data)

        return $serializer->serialize($user, 'json');
    }
}
