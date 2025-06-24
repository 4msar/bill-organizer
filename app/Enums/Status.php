<?php

namespace App\Enums;

use App\Traits\ArrayableEnum;

enum Status: string
{
    use ArrayableEnum;

    case Draft = 'draft';
    case Active = 'active';
    case Expired = 'expired';
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Approved = 'approved';
    case Submitted = 'submitted';
    case Cancelled = 'cancelled';
    case Suspended = 'suspended';
    case Completed = 'completed';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Archived = 'archived';
    case Deleted = 'deleted';
    case UnderReview = 'under_review';
    case Resolved = 'resolved';
    case Failed = 'failed';
    case Inactive = 'inactive';
    case New = 'new';
    case Renew = 'renew';
    case PendingApproval = 'pending_approval';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Pending => 'Pending',
            self::Rejected => 'Rejected',
            self::Approved => 'Approved',
            self::Cancelled => 'Cancelled',
            self::Suspended => 'Suspended',
            self::Completed => 'Completed',
            self::InProgress => 'In Progress',
            self::OnHold => 'On Hold',
            self::Archived => 'Archived',
            self::Deleted => 'Deleted',
            self::UnderReview => 'Under Review',
            self::Resolved => 'Resolved',
            self::Failed => 'Failed',
            self::Inactive => 'Inactive',
            self::New => 'New',
            self::Renew => 'Renew',
            self::PendingApproval => 'Pending Approval'
        };
    }
}
