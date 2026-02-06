<?php

namespace App\Actions\Payments;

use App\Contracts\Action;
use Illuminate\Http\UploadedFile;

final class UploadPaymentAttachmentAction implements Action
{
    /**
     * Upload payment attachment to storage
     *
     * @param  UploadedFile  $file
     * @return string|null File path or null
     */
    public function execute(mixed ...$params): ?string
    {
        $file = $params[0] ?? null;

        if (! $file instanceof UploadedFile) {
            return null;
        }

        return $file->storePublicly('attachments');
    }
}
