<?php
namespace App\Service;

class BookingService
{
    private string $bookingsFile;

    public function __construct(string $bookingsFile)
    {
        $this->bookingsFile = $bookingsFile;
        if (!file_exists($this->bookingsFile))
        {
            file_put_contents($this->bookingsFile, "phone,cottage_id,comment\n");
        }
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

    public function updateBookingComment(string $phone, int $cottageId, string $newComment): bool
    {
        $lines = file($this->bookingsFile);
        $updated = false;

        foreach ($lines as &$line)
        {
            $data = str_getcsv($line);
            if ($data[0] === $phone && (int)$data[1] === $cottageId)
            {
                $data[2] = $newComment;
                $line = implode(',', $data) . "\n";
                $updated = true;
                break;
            }
        }

        if ($updated)
        {
            file_put_contents($this->bookingsFile, implode('', $lines));
        }

        return $updated;
    }

    public function deleteBooking(string $phone, int $cottageId): bool
    {
        $lines = file($this->bookingsFile);
        $found = false;
        $result = [];

        foreach ($lines as $line)
        {
            $data = str_getcsv($line);
            if ($data[0] === $phone && (int)$data[1] === $cottageId)
            {
                $found = true;
                continue;
            }
            $result[] = $line;
        }

        if ($found)
        {
            file_put_contents($this->bookingsFile, implode('', $result));
        }

        return $found;
    }
}