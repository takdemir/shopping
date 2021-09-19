<?php


namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{

    /**
     * @description An error must occur because password is invalid
     */
    public function testCreate(): void
    {

        // php bin/phpunit --testdox
        // php bin/phpunit  --testdox  --filter  testCreate tests/UserControllerTest.php
        // php bin/phpunit  --testdox  --filter 'App\\Tests'
        /*
         * 1. Unit test : Only one function test
         * 2. Integration test: Combination of functions also services
         * 3. Application Test: HTTP requests with complete application test
         */

        // Must response invalid password response
        $postedData = [
            'email' => 'testCase@email.com',
            'name' => 'Test Name',
            'password' => 'AbcdeTest123', // Must be at least 8 characters and include one upper case letter and one lower case letter one digit and one special character
            'roles' => ['ROLE_CUSTOMER']
        ];

        $client = self::createClient([],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_API_TOKEN' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InRhbmVyeXpiQGhvdG1haWwuY29tIn0.fUlXZ_sN5tisKgxjt2HSRcHJCjFLfVqV5vcAV0dkL44'
            ]
        );

        $client->jsonRequest('POST',
            '/api/v1/user',
            $postedData,
        );

        // Started to test for response
        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertIsArray($response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('message', $response);
        self::assertEquals(false, $response['status']);
        self::assertEquals(0, strpos($response['message'], 'Invalid password'));

        

        // Must response invalid email response
        $postedData = [
            'email' => 'testCase@email', // email error
            'name' => 'Test Name',
            'password' => 'AbcdeTest123*',
            'roles' => 'ROLE_USER'
        ];

        $client->jsonRequest('POST',
            '/api/v1/user',
            $postedData,
        );

        // Started to test for response
        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertIsArray($response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('message', $response);
        self::assertEquals(false, $response['status']);
        self::assertEquals(0, strpos($response['message'], 'Invalid email'));
    }

}