<?php
namespace App\Service;

class CottageService
{
    private string $dataFile;

    public function __construct(string $dataFile)
    {
        $this->dataFile = $dataFile;
        if (!file_exists($this->dataFile))
        {
            file_put_contents($this->dataFile, "id,title,amenities,beds,distanceToSea\n");
        }
    }

    public function getAvailableCottages(): array
    {
        $cottages = [];
        if (($handle = fopen($this->dataFile, 'r')) !== false)
        {
            $headers = fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false)
            {
                $cottage = array_combine($headers, $data);
                if ($cottage['isAvailable'] === '1')
                {
                    $cottages[] = [
                        'id' => (int)$cottage['id'],
                        'title' => $cottage['title'],
                        'amenities' => $cottage['amenities'],
                        'beds' => (int)$cottage['beds'],
                        'distanceToSea' => (int)$cottage['distanceToSea']
                    ];
                }
            }
            fclose($handle);
        }

        return $cottages;
    }
}