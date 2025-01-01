<?php

<?php

namespace App\Services;

use App\Mailers\AppMailer;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $mailer;

    public function __construct(AppMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Notify users about a newly created job
     * @param \App\Models\Job $job
     * @param array $data Additional data for the notification
     */
    public function notifyJobCreated($job, $data = [])
    {
        try {
            $user = $job->user;
            $email = $user->email;
            $name = $user->name;
            $subject = "New Job Created: #{$job->id}";
            $data['job'] = $job;
            $data['user'] = $user;

            // Send email
            $this->mailer->send($email, $name, $subject, 'emails.job-created', $data);

            // Log notification
            Log::info("Notification sent for job creation: Job ID #{$job->id}");

        } catch (\Exception $e) {
            Log::error("Error notifying job creation: " . $e->getMessage());
        }
    }

    /**
     * Notify users about a job status change
     * @param \App\Models\Job $job
     * @param string $oldStatus
     * @param string $newStatus
     */
    public function notifyJobStatusChange($job, $oldStatus, $newStatus)
    {
        try {
            $user = $job->user;
            $email = $user->email;
            $name = $user->name;
            $subject = "Job Status Changed: #{$job->id}";
            $data = [
                'user' => $user,
                'job' => $job,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ];

            // Send email notification
            $this->mailer->send($email, $name, $subject, 'emails.job-status-change', $data);

            // Log the status change
            Log::info("Notification sent for job status change: Job ID #{$job->id}, Status: {$oldStatus} -> {$newStatus}");

        } catch (\Exception $e) {
            Log::error("Error notifying job status change: " . $e->getMessage());
        }
    }

    /**
     * Notify translators about available jobs
     * @param array $translators
     * @param \App\Models\Job $job
     */
    public function notifyTranslators($translators, $job)
    {
        foreach ($translators as $translator) {
            try {
                $email = $translator->email;
                $name = $translator->name;
                $subject = "New Translation Job Available: #{$job->id}";
                $data = [
                    'translator' => $translator,
                    'job' => $job,
                ];

                // Send email to translators
                $this->mailer->send($email, $name, $subject, 'emails.new-job-available', $data);

                // Log the notification
                Log::info("Notification sent to translator: {$email} for Job ID #{$job->id}");

            } catch (\Exception $e) {
                Log::error("Error notifying translator ({$translator->email}): " . $e->getMessage());
            }
        }
    }

    /**
     * Generic notification method
     * @param string $email
     * @param string $name
     * @param string $subject
     * @param string $view
     * @param array $data
     */
    public function sendNotification($email, $name, $subject, $view, $data = [])
    {
        try {
            $this->mailer->send($email, $name, $subject, $view, $data);
            Log::info("Notification sent to {$email} with subject: {$subject}");
        } catch (\Exception $e) {
            Log::error("Error sending notification to {$email}: " . $e->getMessage());
        }
    }
}
