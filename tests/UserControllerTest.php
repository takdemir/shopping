<?php


namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{

    /**
     * @description An error must occur because password is invalid
     */
    public function testCreateUser(): void
    {

        // php ./vendor/bin/phpunit --testdox
        // php ./vendor/bin/phpunit  --testdox  --filter  testCreateUser tests/UserControllerTest.php
        // php ./vendor/bin/phpunit  --testdox  --filter 'App\\Tests'
        /*
         * 1. Unit test : Only one function test
         * 2. Integration test: Combination of functions also services
         * 3. Application Test: HTTP request with complete application test
         */

        $postedData = [
            'email' => 'testCase@email.com',
            'firstName' => 'Test First Name',
            'lastName' => 'Test Last Name',
            'password' => 'AbcdeTest123', // Must be at least 8 characters and include one upper case letter and one lower case letter one digit and one special character
            'role' => 'ROLE_USER'
        ];

        $roles = ['ROLE_USER'];
        $acceptedRoles = ['ROLE_USER', 'ROLE_ADMIN'];
        if (array_key_exists('role', $postedData) && in_array($postedData['role'], $acceptedRoles)) {
            $roles = [$postedData['role']];
        }

        $client = self::createClient([],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_API_TOKEN' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InRhbmVyLmFrZGVtaXJAYmFzaXN0ZWsuY29tIn0.i8dAVapTyvlM2W3bJpOxbnGfBxrpL3UekDZsyMgHlW8'
            ]
        );

        $client->jsonRequest('POST',
            '/api/v1/user/create-user',
            $postedData,
        );

        // Started to test for response
        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertIsArray($response);
        self::assertArrayHasKey('success', $response);
        self::assertArrayHasKey('data', $response);
        self::assertArrayHasKey('message', $response);
        self::assertEquals(false, $response['success']);
        self::assertEquals(0, strpos($response['message'], 'Invalid password'));

        

        $postedData = [
            'email' => 'testCase@email', // email error
            'firstName' => 'Test First Name',
            'lastName' => 'Test Last Name',
            'password' => 'AbcdeTest123*',
            'role' => 'ROLE_USER'
        ];

        $roles = ['ROLE_USER'];
        $acceptedRoles = ['ROLE_USER', 'ROLE_ADMIN'];
        if (array_key_exists('role', $postedData) && in_array($postedData['role'], $acceptedRoles)) {
            $roles = [$postedData['role']];
        }

        $client->jsonRequest('POST',
            '/api/v1/user/create-user',
            $postedData,
        );

        // Started to test for response
        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertIsArray($response);
        self::assertArrayHasKey('success', $response);
        self::assertArrayHasKey('data', $response);
        self::assertArrayHasKey('message', $response);
        self::assertEquals(false, $response['success']);
        self::assertEquals(0, strpos($response['message'], 'Invalid email'));
    }

}