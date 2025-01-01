<?php

namespace App\Services;

use App\Repositories\BookingRepository;

class BookingService
{
    protected $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function getUserJobsWithHistory($userId)
    {
        $jobs = $this->bookingRepository->getUserJobs($userId);
        return [
            'active_jobs' => $jobs->where('status', 'active'),
            'completed_jobs' => $jobs->where('status', 'completed')
        ];
    }

    public function createJob($data)
    {
        return $this->bookingRepository->storeJob($data);
    }
}
