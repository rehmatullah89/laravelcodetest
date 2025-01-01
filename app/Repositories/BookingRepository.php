<?php

namespace App\Repositories;

use App\Models\Job;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ValidationService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingRepository extends BaseRepository
{
    protected $notificationService;
    protected $validationService;

    public function __construct(Job $job, NotificationService $notificationService, ValidationService $validationService)
    {
        parent::__construct($job);
        $this->notificationService = $notificationService;
        $this->validationService = $validationService;
    }

    /**
     * Get all jobs for a specific user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserJobs($userId)
    {
        return $this->model->where('user_id', $userId)->with('language', 'translator')->get();
    }

    /**
     * Store a new job.
     *
     * @param array $data
     * @return \App\Models\Job
     */
    public function storeJob(array $data)
    {
        $this->validationService->validate($data, $this->validationService->jobCreationRules());

        $job = $this->create($data);

        // Notify user about the new job
        $this->notificationService->notifyJobCreated($job);

        return $job;
    }

    /**
     * Update a job's details.
     *
     * @param int $jobId
     * @param array $data
     * @return \App\Models\Job
     */
    public function updateJob($jobId, array $data)
    {
        $this->validationService->validate($data, $this->validationService->jobUpdateRules());

        $job = $this->findOrFail($jobId);

        $job->fill($data);
        $job->save();

        // Notify user about job updates
        if (isset($data['status'])) {
            $this->notificationService->notifyJobStatusChange($job, $job->getOriginal('status'), $job->status);
        }

        return $job;
    }

    /**
     * Assign a translator to a job.
     *
     * @param int $jobId
     * @param int $translatorId
     * @return \App\Models\Job
     */
    public function assignTranslator($jobId, $translatorId)
    {
        $job = $this->findOrFail($jobId);

        if ($job->status !== 'pending') {
            throw new ValidationException('Job is not available for assignment.');
        }

        $job->translator_id = $translatorId;
        $job->status = 'assigned';
        $job->save();

        // Notify translator about the assignment
        $translator = User::find($translatorId);
        $this->notificationService->notifyTranslators([$translator], $job);

        return $job;
    }

    /**
     * Cancel a job.
     *
     * @param int $jobId
     * @param int $cancelledByUserId
     * @return \App\Models\Job
     */
    public function cancelJob($jobId, $cancelledByUserId)
    {
        $job = $this->findOrFail($jobId);

        if ($job->status === 'completed') {
            throw new ValidationException('Completed jobs cannot be canceled.');
        }

        $job->status = 'canceled';
        $job->cancelled_by = $cancelledByUserId;
        $job->save();

        // Notify the user and translator about the cancellation
        $this->notificationService->notifyJobStatusChange($job, 'active', 'canceled');

        return $job;
    }

    /**
     * End a job session.
     *
     * @param int $jobId
     * @return \App\Models\Job
     */
    public function endJob($jobId)
    {
        $job = $this->findOrFail($jobId);

        if ($job->status !== 'assigned') {
            throw new ValidationException('Only assigned jobs can be ended.');
        }

        $job->status = 'completed';
        $job->completed_at = Carbon::now();
        $job->save();

        // Notify the user about job completion
        $this->notificationService->notifyJobStatusChange($job, 'assigned', 'completed');

        return $job;
    }

    /**
     * Get potential jobs for a translator.
     *
     * @param int $translatorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPotentialJobsForTranslator($translatorId)
    {
        $translator = User::findOrFail($translatorId);

        // Assuming translator languages and other filters
        $translatorLanguages = $translator->languages->pluck('id')->toArray();

        return $this->model
            ->where('status', 'pending')
            ->whereIn('language_id', $translatorLanguages)
            ->get();
    }
}
