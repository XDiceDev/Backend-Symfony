<?php

namespace App\Tests\Controller;

use App\Service\BookingService;
use App\Service\CottageService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CottageApiControllerTest extends WebTestCase
{
    private $client;
    private $cottageServiceMock;
    private $bookingServiceMock;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        
        $this->cottageServiceMock = $this->createMock(CottageService::class);
        $this->bookingServiceMock = $this->createMock(BookingService::class);

        $this->client->getContainer()->set(CottageService::class, $this->cottageServiceMock);
        $this->client->getContainer()->set(BookingService::class, $this->bookingServiceMock);
    }

    public function testGetCottages(): void
    {
        $cottages = [
            ['id' => 1, 'title' => 'Beach House', 'amenities' => 'WiFi|Pool', 'beds' => 4, 'distanceToSea' => 100],
            ['id' => 2, 'title' => 'Mountain Cabin', 'amenities' => 'Fireplace', 'beds' => 2, 'distanceToSea' => 1000]
        ];

        $this->cottageServiceMock
            ->expects($this->once())
            ->method('getAvailableCottages')
            ->willReturn($cottages);

        $this->client->request('GET', '/api/cottages');

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals($cottages, json_decode($response->getContent(), true));
    }

    public function testCreateBookingSuccess(): void
    {
        $requestData = [
            'phone' => '+1234567890',
            'cottageId' => 1,
            'comment' => 'Test booking'
        ];

        $this->bookingServiceMock
            ->expects($this->once())
            ->method('createBooking')
            ->with(
                $this->equalTo($requestData['phone']),
                $this->equalTo($requestData['cottageId']),
                $this->equalTo($requestData['comment'])
            );

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals(['status' => 'Booking created successfully'], json_decode($response->getContent(), true));
    }

    public function testCreateBookingMissingFields(): void
    {
        $requestData = [
            'phone' => '+1234567890'
        ];

        $this->bookingServiceMock
            ->expects($this->never())
            ->method('createBooking');

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals(['error' => 'Phone and cottageId are required'], json_decode($response->getContent(), true));
    }

    public function testUpdateCommentSuccess(): void
    {
        $phone = '+1234567890';
        $cottageId = 1;
        $comment = 'Updated comment';

        $this->bookingServiceMock
            ->expects($this->once())
            ->method('updateBookingComment')
            ->with($phone, $cottageId, $comment)
            ->willReturn(true);

        $this->client->request(
            'PUT',
            '/api/bookings/edit/',
            ['phone' => $phone, 'cottageId' => $cottageId, 'comment' => $comment]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals(['status' => 'Comment updated'], json_decode($response->getContent(), true));
    }

    public function testUpdateCommentNotFound(): void
    {
        $phone = '+1234567890';
        $cottageId = 1;
        $comment = 'Updated comment';

        $this->bookingServiceMock
            ->expects($this->once())
            ->method('updateBookingComment')
            ->with($phone, $cottageId, $comment)
            ->willReturn(false);

        $this->client->request(
            'PUT',
            '/api/bookings/edit/',
            ['phone' => $phone, 'cottageId' => $cottageId, 'comment' => $comment]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals(['error' => 'Booking not found'], json_decode($response->getContent(), true));
    }

    public function testDeleteBookingSuccess(): void
    {
        $phone = '+1234567890';
        $cottageId = 1;

        $this->bookingServiceMock
            ->expects($this->once())
            ->method('deleteBooking')
            ->with($phone, $cottageId)
            ->willReturn(true);

        $this->client->request(
            'DELETE',
            '/api/bookings/delete/',
            ['phone' => $phone, 'cottageId' => $cottageId]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals(['status' => 'Booking deleted'], json_decode($response->getContent(), true));
    }

    public function testDeleteBookingNotFound(): void
    {
        $phone = '+1234567890';
        $cottageId = 1;

        $this->bookingServiceMock
            ->expects($this->once())
            ->method('deleteBooking')
            ->with($phone, $cottageId)
            ->willReturn(false);

        $this->client->request(
            'DELETE',
            '/api/bookings/delete/',
            ['phone' => $phone, 'cottageId' => $cottageId]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals(['error' => 'Booking not found'], json_decode($response->getContent(), true));
    }
}