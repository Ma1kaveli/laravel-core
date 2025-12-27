<?php

namespace Tests\Fakes;

use Core\Repositories\BaseRepository;

class FakeRepository extends BaseRepository
{
    public function __construct($user = null)
    {
        parent::__construct(FakeRepositoryModel::class, $user);
    }
}
