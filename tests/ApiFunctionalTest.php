<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\DomCrawler\Crawler;

class ApiFunctionalTest extends WebTestCase
{

    /** @var Client */
    protected $client;

    protected function setUp()
    {
        $this->client = self::createClient();
    }

    public function testApi()
    {
        // generate random user and password
        // if we want to run tests from a IDE generate for every test another email and password
        $email = "user_" . rand(1000, 5000) . "@example.com";
        $password = "pwd_" . rand(1000, 5000);
        $this->client->enableProfiler();

        // sign-up, test create new user
        $userId = $this->signUpTest($email, $password);

        // check if email is send
        $validationUrl = $this->checkIfEmailIsSend();

        // test failed to login when user is not activated
        $this->loginFailedTest($email, $password);

        // test check existing email
        $this->checkEmailTest($email);

        // validate url
        $this->validateUrlTest($validationUrl);

        // test successfully login when user  activated
        $this->loginSuccessfullyTest($email, $password, $userId);
   }

   public function testFailedLoginWhenEmailDoNotExists()
   {
       $url = "/api/v1/login";
       $content = json_encode(['email' => "some@example.com", 'password' => "12"]);
       $this->client->request('POST', $url, [], [], ['CONTENT_TYPE' => 'application/json'], $content);

       $response = json_decode($this->client->getResponse()->getContent());

       $this->assertEquals($response->message, "Failed to login.");
   }

   public function testFailedCheckEmailWhenInvalidEmailIsProvided()
   {
       $url = "/api/v1/check?email=some_invalid@example.com";
       $this->client->request('GET', $url);
       $response = json_decode($this->client->getResponse()->getContent());

       $this->assertEquals($response->message, "Email invalid or do not exists");
   }

    /**
     * @param string $email
     * @param string $password
     * @return \stdClass $userId
     */
    protected function signUpTest(string $email, string $password)
    {
        $url = "/api/v1/sign-up";
        $content = json_encode(['email' => $email, 'password' => $password]);
        $this->client->request('POST', $url, [], [], ['CONTENT_TYPE' => 'application/json'], $content);

        $response = json_decode($this->client->getResponse()->getContent());

        // test if the user created have an id and have the email that we send
        $this->assertTrue($response->id > 0);
        $this->assertEquals($response->email, $email);

        return $response->id;
    }

    protected function checkEmailTest(string $email)
    {
        $url = "/api/v1/check?email=". $email;
        $this->client->request('GET', $url);
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertTrue($response->id > 0);
        $this->assertEquals($response->email, $email);
    }

    protected function loginFailedTest($email, $password)
    {
        $url = "/api/v1/login";
        $content = json_encode(['email' => $email, 'password' => $password]);
        $this->client->request('POST', $url, [], [], ['CONTENT_TYPE' => 'application/json'], $content);

        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($response->message, "Failed to login.");
    }

    protected function loginSuccessfullyTest($email, $password, $userId)
    {
        $url = "/api/v1/login";
        $content = json_encode(['email' => $email, 'password' => $password]);
        $this->client->request('POST', $url, [], [], ['CONTENT_TYPE' => 'application/json'], $content);

        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($response->id, $userId);
        $this->assertEquals($response->email, $email);

    }

    protected function validateUrlTest($url)
    {
        $this->client->request('GET', $url);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('Activation successfully', $response);
    }

    /**
     * Return validation url from email.
     *
     * @return string $validationUrl
     */
    protected function checkIfEmailIsSend()
    {
        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        $this->assertSame(1, $mailCollector->getMessageCount());
        $this->assertInstanceOf('Swift_Message', $message);

        // create a dom crawler from email message
        $crawler = new Crawler($message->getBody());
        $validateUrl = $crawler->filterXPath('//body/a')->attr('href');
        $urlParts = parse_url($validateUrl);

        return $urlParts['path'];
    }
}