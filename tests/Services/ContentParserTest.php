<?php

namespace App\Services;


use PHPUnit\Framework\TestCase;

class ContentParserTest extends TestCase
{
    /**
     * @dataProvider generateContent
     *
     * @param $string
     * @param $expected
     */
    public function testParseContent($string, $expected)
    {
        $parser = new ContentParser();
        $result = $parser->parse($string);

        $this->assertEquals($expected, $result);
    }

    public function generateContent()
    {
        return [
            "url" => [
                "email=stelu26@gmail.com&password=123456",
                ['email' => 'stelu26@gmail.com', 'password' => md5('123456')]
            ],
            "json" => [
                '{"email":"stelu26@gmail.com","password":"123456"}',
                ['email' => 'stelu26@gmail.com', 'password' => md5('123456')]
            ],
        ];
    }

    /**
     * Canary test for check if phpunit work
     */
    public function testCanary()
    {
        $this->assertTrue(true);
    }
}
