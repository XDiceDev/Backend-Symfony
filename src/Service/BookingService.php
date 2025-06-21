<?php
namespace App\Service;

class BookingService
{
    private string $bookingsFile;

    public function __construct(string $bookingsFile)
    {
        $this->bookingsFile = $bookingsFile;
    }

    public function createBooking(string $phone, int $cottageId, string $comment = ''): void
    {
        $data = [
            'phone' => $phone,
            'cottage_id' => $cottageId,
            'comment' => $comment
        ];

        $file = fopen($this->bookingsFile, 'a');
        fputcsv($file, $data);
        fclose($file);
    }
}