<?php

namespace App\Services\Storefront;

use App\Models\Review;

class ReviewSubmissionService
{
    public function store(array $data): Review
    {
        $data['status'] = 'pending';

        return Review::create($data);
    }
}
