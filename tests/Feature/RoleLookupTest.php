<?php

use App\Models\User;

it('returns an empty collection when a role does not exist', function () {
    expect(User::usersByRoleName('missing_role'))->toBeEmpty();
});
