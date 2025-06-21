<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;

class CottageApiScenarioTest extends WebTestCase
{
    private string $kernelDir;
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->kernelDir = static::$kernel->getProjectDir();
        
        $this->client->getContainer()->set(
            \App\Service\CottageService::class,
            new \App\Service\CottageService(
                Path::join($this->kernelDir, 'tests/resources/cottages_test.csv')
            )
        );
        
        $this->client->getContainer()->set(
            \App\Service\BookingService::class,
            new \App\Service\BookingService(
                Path::join($this->kernelDir, 'tests/resources/bookings_test.csv')
            )
        );
    }

    public function testGetCottages()
    {
        $this->client->request('GET', '/api/cottages');

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals('Beach Cottage', $responseData[0]['title']);
        $this->assertEquals('Ocean View', $responseData[1]['title']);
    }

    public function testCreateBooking()
    {
        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => '+1234567890',
                'cottageId' => 1,
                'comment' => 'Test booking'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Booking created successfully']),
            $this->client->getResponse()->getContent()
        );
    }

    public function testCreateBookingMissingParameters()
    {
        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['cottageId' => 1])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Phone and cottageId are required']),
            $this->client->getResponse()->getContent()
        );
    }

    public function testUpdateBookingComment()
    {
        $this->client->getContainer()->get(\App\Service\BookingService::class)->createBooking(
            '+1234567890',
            1,
            'Initial comment'
        );

        $this->client->request(
            'PUT',
            '/api/bookings/edit/',
            [
                'phone' => '+1234567890',
                'cottageId' => 1,
                'comment' => 'Updated comment'
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Comment updated']),
            $this->client->getResponse()->getContent()
        );
    }

    public function testUpdateNonExistentBooking()
    {
        $this->client->request(
            'PUT',
            '/api/bookings/edit/',
            [
                'phone' => '+9999999999',
                'cottageId' => 999,
                'comment' => 'Test comment'
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Booking not found']),
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeleteBooking()
    {
        $this->client->getContainer()->get(\App\Service\BookingService::class)->createBooking(
            '+1234567890',
            1,
            'Test booking'
        );

        $this->client->request(
            'DELETE',
            '/api/bookings/delete/',
            [
                'phone' => '+1234567890',
                'cottageId' => 1
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Booking deleted']),
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeleteNonExistentBooking()
    {
        $this->client->request(
            'DELETE',
            '/api/bookings/delete/',
            [
                'phone' => '+9999999999',
                'cottageId' => 999
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Booking not found']),
            $this->client->getResponse()->getContent()
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $bookingsFile = Path::join($this->kernelDir, 'tests/resources/bookings_test.csv');
        if (file_exists($bookingsFile)) {
            file_put_contents($bookingsFile, "phone,cottage_id,comment\n");
        }
    }
}