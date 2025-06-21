<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\Cottage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CottageApiControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Cottage')->execute();
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        parent::tearDown();
    }

    public function testGetCottages(): void
    {
        $cottage1 = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true);
        $cottage2 = (new Cottage())
            ->setName('Mountain Cabin')
            ->setAmenities('Fireplace')
            ->setBedCount(2)
            ->setRowFromSea(1000)
            ->setIsAvailable(true);
        $cottage3 = (new Cottage())
            ->setName('City Apartment')
            ->setAmenities('Kitchen')
            ->setBedCount(3)
            ->setRowFromSea(500)
            ->setIsAvailable(false);

        $this->entityManager->persist($cottage1);
        $this->entityManager->persist($cottage2);
        $this->entityManager->persist($cottage3);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/cottages');

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Должен возвращать статус 200');
        $this->assertJson($response->getContent(), 'Ответ должен быть в формате JSON');
        $this->assertEquals([
            [
                'id' => $cottage1->getId(),
                'name' => 'Beach House',
                'amenities' => 'WiFi|Pool',
                'bedCount' => 4,
                'rowFromSea' => 100
            ],
            [
                'id' => $cottage2->getId(),
                'name' => 'Mountain Cabin',
                'amenities' => 'Fireplace',
                'bedCount' => 2,
                'rowFromSea' => 1000
            ]
        ], json_decode($response->getContent(), true), 'Должен возвращать только доступные коттеджи');
    }

    public function testCreateBookingSuccess(): void
    {
        $cottage = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true);
        $this->entityManager->persist($cottage);
        $this->entityManager->flush();

        $requestData = [
            'phone' => '+1234567890',
            'cottageId' => $cottage->getId(),
            'comment' => 'Test booking'
        ];

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode(), 'Должен возвращать статус 201');
        $this->assertJson($response->getContent(), 'Ответ должен быть в формате JSON');
        $this->assertEquals(['status' => 'Booking created successfully'], json_decode($response->getContent(), true), 'Должен возвращать сообщение об успешном создании брони');
    }

    public function testCreateBookingMissingFields(): void
    {
        $requestData = [
            'phone' => '+1234567890'
        ];

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Должен возвращать статус 400');
        $this->assertJson($response->getContent(), 'Ответ должен быть в формате JSON');
        $this->assertEquals(['error' => 'Phone and cottageId are required'], json_decode($response->getContent(), true), 'Должен возвращать ошибку о пропущенных полях');
    }

    public function testUpdateCommentSuccess(): void
    {
        $cottage = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true);
        $this->entityManager->persist($cottage);

        $booking = (new Booking())
            ->setPhone('+1234567890')
            ->setCottage($cottage)
            ->setComment('Initial comment');
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        $phone = '+1234567890';
        $cottageId = $cottage->getId();
        $comment = 'Updated comment';

        $this->client->request(
            'PUT',
            '/api/bookings/edit/',
            ['phone' => $phone, 'cottageId' => $cottageId, 'comment' => $comment]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Должен возвращать статус 200');
        $this->assertJson($response->getContent(), 'Ответ должен быть в формате JSON');
        $this->assertEquals(['status' => 'Comment updated'], json_decode($response->getContent(), true), 'Должен возвращать сообщение об обновлении комментария');
    }

    public function testUpdateCommentNotFound(): void
    {
        $cottage = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true);
        $this->entityManager->persist($cottage);
        $this->entityManager->flush();

        $phone = '+1234567890';
        $cottageId = $cottage->getId();
        $comment = 'Updated comment';

        $this->client->request(
            'PUT',
            '/api/bookings/edit/',
            ['phone' => $phone, 'cottageId' => $cottageId, 'comment' => $comment]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode(), 'Должен возвращать статус 404');
        $this->assertJson($response->getContent(), 'Ответ должен быть в формате JSON');
        $this->assertEquals(['error' => 'Booking not found'], json_decode($response->getContent(), true), 'Должен возвращать ошибку о ненайденном бронировании');
    }

    public function testDeleteBookingSuccess(): void
    {
        $cottage = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true);
        $this->entityManager->persist($cottage);

        $booking = (new Booking())
            ->setPhone('+1234567890')
            ->setCottage($cottage)
            ->setComment('Test booking');
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        $phone = '+1234567890';
        $cottageId = $cottage->getId();

        $this->client->request(
            'DELETE',
            '/api/bookings/delete/',
            ['phone' => $phone, 'cottageId' => $cottageId]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Должен возвращать статус 200');
        $this->assertJson($response->getContent(), 'Ответ должен быть в формате JSON');
        $this->assertEquals(['status' => 'Booking deleted'], json_decode($response->getContent(), true), 'Должен возвращать сообщение об удалении брони');
    }

    public function testDeleteBookingNotFound(): void
    {
        $cottage = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true);
        $this->entityManager->persist($cottage);
        $this->entityManager->flush();

        $phone = '+1234567890';
        $cottageId = $cottage->getId();

        $this->client->request(
            'DELETE',
            '/api/bookings/delete/',
            ['phone' => $phone, 'cottageId' => $cottageId]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode(), 'Должен возвращать статус 404');
        $this->assertJson($response->getContent(), 'Ответ должен быть в формате JSON');
        $this->assertEquals(['error' => 'Booking not found'], json_decode($response->getContent(), true), 'Должен возвращать ошибку о ненайденном бронировании');
    }
}