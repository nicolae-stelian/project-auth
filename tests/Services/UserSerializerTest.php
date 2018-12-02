<?php

namespace App\Services;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserSerializerTest extends TestCase
{
    public function testConvertUserToJsonString()
    {
        $user = new User("name@example.com", "pwd");
        $serializer = new UserSerializer();

        $json = $serializer->toJsonString($user);
        $expected = '{"id":null,"email":"name@example.com"}';

        $this->assertEquals($expected, $json);
    }
}
