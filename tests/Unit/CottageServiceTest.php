<?php

namespace App\Tests\Service;

use App\Service\CottageService;
use PHPUnit\Framework\TestCase;

class CottageServiceTest extends TestCase
{
    private string $tempFile;
    private CottageService $cottageService;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'cottages_test_');
        $this->cottageService = new CottageService($this->tempFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile))
        {
            unlink($this->tempFile);
        }
    }

    public function testGetAvailableCottagesEmptyFile(): void
    {
        $cottages = $this->cottageService->getAvailableCottages();
        $this->assertIsArray($cottages, 'Should return an array');
        $this->assertEmpty($cottages, 'Should return empty array for empty file');
    }

    public function testGetAvailableCottagesWithAvailableCottages(): void
    {
        $testData = [
            'id,title,amenities,beds,distanceToSea,isAvailable',
            '1,Beach House,WiFi|Pool,4,100,1',
            '2,Mountain Cabin,Fireplace,2,1000,1',
            '3,City Apartment,Kitchen,3,500,0'
        ];
        file_put_contents($this->tempFile, implode("\n", $testData) . "\n");

        $cottages = $this->cottageService->getAvailableCottages();

        $this->assertCount(2, $cottages, 'Should return only available cottages');
        $this->assertEquals([
            [
                'id' => 1,
                'title' => 'Beach House',
                'amenities' => 'WiFi|Pool',
                'beds' => 4,
                'distanceToSea' => 100
            ],
            [
                'id' => 2,
                'title' => 'Mountain Cabin',
                'amenities' => 'Fireplace',
                'beds' => 2,
                'distanceToSea' => 1000
            ]
        ], $cottages, 'Should return correct cottage data');
    }

    public function testGetAvailableCottagesWithNoAvailableCottages(): void
    {
        $testData = [
            'id,title,amenities,beds,distanceToSea,isAvailable',
            '1,Beach House,WiFi|Pool,4,100,0',
            '2,Mountain Cabin,Fireplace,2,1000,0'
        ];
        file_put_contents($this->tempFile, implode("\n", $testData) . "\n");

        $cottages = $this->cottageService->getAvailableCottages();

        $this->assertIsArray($cottages, 'Should return an array');
        $this->assertEmpty($cottages, 'Should return empty array when no cottages are available');
    }

    public function testGetAvailableCottagesWithInvalidFile(): void
    {
        file_put_contents($this->tempFile, "invalid,data\n");

        $cottages = $this->cottageService->getAvailableCottages();

        $this->assertIsArray($cottages, 'Should return an array even with invalid data');
        $this->assertEmpty($cottages, 'Should return empty array for invalid file format');
    }
}