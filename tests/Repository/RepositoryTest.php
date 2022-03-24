<?php
/**
 * Created by PhpStorm.
 * User: Lloric Mayuga Garcia <lloricode@gmail.com>
 * Date: 1/1/19
 * Time: 8:39 AM
 */

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertTrue;

beforeEach(fn() => $this->loggedInAs());

test('pagination enable skip then limit zero', function (
    bool $enableSkip,
    int $limitRequest,
    int $limitDefault,
    int $addRoleCount,
    int $expectedCount,
    string $queryParamLimit = null
) {
    $queryParamLimit = is_null($queryParamLimit) ? '' : "?limit=$queryParamLimit";

    config(
        [
            'setting.repository.skip_pagination' => $enableSkip,
            'setting.repository.limit_pagination' => $limitRequest,
            'repository.pagination.limit' => $limitDefault,
        ]
    );

    $roleModel = app(config('permission.models.role'));
    $addRoleCount -= $roleModel::count();// exclude count seeded role

    foreach (range(1, $addRoleCount) as $i) {
        $roleModel::create(
            [
                'name' => 'role test '.$i,
            ]
        );
    }

    $response = get(route('backend.roles.index').$queryParamLimit, $this->addHeaders());

    $content = ((array)json_decode($response->response->getContent()));

    $isContentHasData = isset($content['data']);

    assertTrue($isContentHasData);

    if ($isContentHasData) {
        assertCount($expectedCount, $content['data']);
    }
})
    ->with(
    /**
     * 1 bool $enableSkip,
     * 2 int $limitRequest,
     * 3 int $limitDefault,
     * 4 int $addRoleCount,
     * 5 int $expectedCount,
     * 6 string $queryParamLimit = null
     */
        [
            'default behavior' => [true, 100, 15, 20, 15],
            'default behavior with disable skip' => [false, 100, 15, 20, 15],
            '.' => [true, 100, 15, 100, 50, '50'],
            '..' => [false, 100, 15, 20, 20, '100'],

            // request limit non numeric
            'request limit non numeric default behavior' => [true, 100, 15, 20, 15, 'ccc'],
            'request limit non numeric default behavior with disable skip' => [false, 100, 15, 20, 15, 'ccc'],

            // zero
            'zero request limit' => [true, 100, 15, 20, 20, '0'],
            'zero request limit with disable skip' => [false, 100, 15, 20, 15, '0'],

            // invalid request limit
            'negative request limit' => [true, 100, 20, 100, 20, '-1'],
            'exceed max request limit' => [true, 50, 20, 100, 20, '60'],
        ]
    );