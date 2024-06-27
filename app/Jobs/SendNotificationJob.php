<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ExternalAuthService;
use Exception;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $idUser = 0,
        public string $title = '',
        public string|null $message = '',
        public string|null $url = ''
    ) {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        info('Sending notification to user ' . $this->idUser . '...');

        $externalAuthService = new ExternalAuthService();
        $externalNotf = $externalAuthService->sendExternalNotification();

        if (!$externalNotf) {
            throw new Exception('Error sending notification to user ' . $this->idUser . ': external service not available.');
        }

        $notificationModel = new Notification();
        $notificationModel->createNotification([
            'user_id' => $this->idUser,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url
        ]);

        info('Notification sent to user ' . $this->idUser . ' successfully.');
    }

    public function tries(): int
    {
        return 5;
    }
}
