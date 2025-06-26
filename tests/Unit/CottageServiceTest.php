<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Cottage;
use App\Service\CottageService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CottageServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private CottageService $cottageService;

    /**
     * @Override
     */
    protected function setUp(): void
    {
        self::bootKernel();
        /**
         * @var EntityManagerInterface
         */
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->cottageService = new CottageService($this->entityManager);

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

    public function testGetAvailableCottagesEmptyDatabase(): void
    {
        $cottages = $this->cottageService->getAvailableCottages();

        $this->assertEmpty($cottages, 'Должен возвращать пустой массив для пустой базы данных');
    }

    public function testGetAvailableCottagesWithAvailableCottages(): void
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
        $cottage3 = (new Cottage())
            ->setName('City Apartment')
            ->setAmenities('Kitchen')
            ->setBedCount(3)
            ->setRowFromSea(500)
            ->setIsAvailable(false)
        ;

        $this->entityManager->persist($cottage1);
        $this->entityManager->persist($cottage2);
        $this->entityManager->persist($cottage3);
        $this->entityManager->flush();

        $cottages = $this->cottageService->getAvailableCottages();

        $this->assertCount(2, $cottages, 'Должен возвращать только доступные коттеджи');
        $this->assertEquals([
            [
                'id' => $cottage1->getId(),
                'name' => 'Beach House',
                'amenities' => 'WiFi|Pool',
                'bedCount' => 4,
                'rowFromSea' => 100,
            ],
            [
                'id' => $cottage2->getId(),
                'name' => 'Mountain Cabin',
                'amenities' => 'Fireplace',
                'bedCount' => 2,
                'rowFromSea' => 1000,
            ],
        ], array_map(function ($cottage) {
            return [
                'id' => $cottage['id'],
                'name' => $cottage['name'],
                'amenities' => $cottage['amenities'],
                'bedCount' => $cottage['bedCount'],
                'rowFromSea' => $cottage['rowFromSea'],
            ];
        }, $cottages), 'Должен возвращать корректные данные коттеджей');
    }

    public function testGetAvailableCottagesWithNoAvailableCottages(): void
    {
        $cottage1 = (new Cottage())
            ->setName('Beach House')
            ->setAmenities('WiFi|Pool')
            ->setBedCount(4)
            ->setRowFromSea(100)
            ->setIsAvailable(false)
        ;
        $cottage2 = (new Cottage())
            ->setName('Mountain Cabin')
            ->setAmenities('Fireplace')
            ->setBedCount(2)
            ->setRowFromSea(1000)
            ->setIsAvailable(false)
        ;

        $this->entityManager->persist($cottage1);
        $this->entityManager->persist($cottage2);
        $this->entityManager->flush();

        $cottages = $this->cottageService->getAvailableCottages();

        $this->assertEmpty($cottages, 'Должен возвращать пустой массив, если нет доступных коттеджей');
    }

    public function testGetAvailableCottagesWithInvalidData(): void
    {
        $cottage = (new Cottage())
            ->setAmenities('WiFi')
            ->setBedCount(2)
            ->setRowFromSea(100)
            ->setIsAvailable(true)
        ;

        try {
            $this->entityManager->persist($cottage);
            $this->entityManager->flush();
            $this->fail('Ожидалось исключение из-за отсутствия поля name');
        } catch (Exception $e) {
            $this->assertStringContainsString('NOT NULL', $e->getMessage());
        }

        $cottages = $this->cottageService->getAvailableCottages();
        $this->assertEmpty($cottages, 'Должен возвращать пустой массив при некорректных данных');
    }
}
