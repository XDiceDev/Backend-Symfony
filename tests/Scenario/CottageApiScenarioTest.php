<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\Cottage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CottageApiScenarioTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    /**
     * @Override
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        /**
         * @var EntityManagerInterface
         */
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Cottage')->execute();
        $this->entityManager->flush();
    }

    /**
     * @Override
     */
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
            ->setIsAvailable(true)
        ;
        $cottage2 = (new Cottage())
            ->setName('Mountain Cabin')
            ->setAmenities('Fireplace')
            ->setBedCount(2)
            ->setRowFromSea(1000)
            ->setIsAvailable(true)
        ;

        $this->entityManager->persist($cottage1);
        $this->entityManager->persist($cottage2);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/cottages');

        $this->assertResponseIsSuccessful('Должен возвращать статус 200');
        $this->assertJson($this->client->getResponse()->getContent(), 'Ответ должен быть в формате JSON');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $responseData, 'Должен возвращать два коттеджа');
        /**
         * @var array<int, array{name: string}> $responseData
         */
        $this->assertTrue(isset($responseData[0]['name']), 'Первый коттедж должен существовать');
        $this->assertTrue(isset($responseData[1]['name']), 'Второй коттедж должен существовать');
        $this->assertEquals('Beach House', $responseData[0]['name'], 'Первый коттедж должен быть Beach House');
        $this->assertEquals('Mountain Cabin', $responseData[1]['name'], 'Второй коттедж должен быть Mountain Cabin');
    }

    public function testCreateBooking(): void
    {
        $cottage = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true)
        ;
        $this->entityManager->persist($cottage);
        $this->entityManager->flush();

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => '+1234567890',
                'cottageId' => $cottage->getId(),
                'comment' => 'Test booking',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED, 'Должен возвращать статус 201');
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Booking created successfully']),
            $this->client->getResponse()->getContent(),
            'Должен возвращать сообщение об успешном создании брони'
        );
    }

    public function testCreateBookingMissingParameters(): void
    {
        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['cottageId' => 1])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST, 'Должен возвращать статус 400');
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Phone and cottageId are required']),
            $this->client->getResponse()->getContent(),
            'Должен возвращать ошибку о пропущенных полях'
        );
    }

    public function testUpdateBookingComment(): void
    {
        $cottage = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true)
        ;
        $this->entityManager->persist($cottage);

        $booking = (new Booking())
            ->setPhone('+1234567890')
            ->setCottage($cottage)
            ->setComment('Initial comment')
        ;
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        $this->client->request(
            'PUT',
            '/api/bookings/edit/',
            [
                'phone' => '+1234567890',
                'cottageId' => $cottage->getId(),
                'comment' => 'Updated comment',
            ]
        );

        $this->assertResponseIsSuccessful('Должен возвращать статус 200');
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Comment updated']),
            $this->client->getResponse()->getContent(),
            'Должен возвращать сообщение об обновлении комментария'
        );
    }

    public function testUpdateNonExistentBooking(): void
    {
        $this->client->request(
            'PUT',
            '/api/bookings/edit/',
            [
                'phone' => '+9999999999',
                'cottageId' => 999,
                'comment' => 'Test comment',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, 'Должен возвращать статус 404');
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Booking not found']),
            $this->client->getResponse()->getContent(),
            'Должен возвращать ошибку о ненайденном бронировании'
        );
    }

    public function testDeleteBooking(): void
    {
        $cottage = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(true)
        ;
        $this->entityManager->persist($cottage);

        $booking = (new Booking())
            ->setPhone('+1234567890')
            ->setCottage($cottage)
            ->setComment('Test booking')
        ;
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        $this->client->request(
            'DELETE',
            '/api/bookings/delete/',
            [
                'phone' => '+1234567890',
                'cottageId' => $cottage->getId(),
            ]
        );

        $this->assertResponseIsSuccessful('Должен возвращать статус 200');
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Booking deleted']),
            $this->client->getResponse()->getContent(),
            'Должен возвращать сообщение об удалении брони'
        );
    }

    public function testDeleteNonExistentBooking(): void
    {
        $this->client->request(
            'DELETE',
            '/api/bookings/delete/',
            [
                'phone' => '+9999999999',
                'cottageId' => 999,
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, 'Должен возвращать статус 404');
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Booking not found']),
            $this->client->getResponse()->getContent(),
            'Должен возвращать ошибку о ненайденном бронировании'
        );
    }
}
