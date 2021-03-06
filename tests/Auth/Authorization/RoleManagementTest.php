<?php
/**
 * Created by PhpStorm.
 * User: Lloric Mayuga Garcia <lloricode@gmail.com>
 * Date: 12/24/18
 * Time: 11:17 AM
 */

uses(Test\Auth\Authorization\BaseRole::class);

it('validation role', function ($routeName) {
    $this->loggedInAs();

    $route = "backend.roles.$routeName";
    $paramNoData = [
        'name' => '',
    ];
    switch ($routeName) {
        case 'store':
            post(route($route), $paramNoData, $this->addHeaders());
            break;
        case 'update':
            put(
                route(
                    $route,
                    [
                        'id' => self::forId($this->createRole()),
                    ]
                ),
                $paramNoData,
                $this->addHeaders()
            );
            break;
    }
    assertResponseStatus(422);
    seeJson(
        [
            'name' => ['The name field is required.'],
        ]
    );
})
    ->with([
        ["store"],
        ["update"],
    ]);

it('default role not allowed', function ($verbMethod, $routeName) {
    $this->loggedInAs();
    $this->{$verbMethod}(
        route(
            $routeName,
            [
                'id' => self::forId($this->getByRoleName('system')),
            ]
        ),
        $verbMethod == 'delete' ? [] : ['name' => $this->getByRoleName('system')->name],
        $this->addHeaders()
    );
    assertResponseStatus(403);
    seeJson(
        [
            'message' => 'You cannot update/delete default role.',
        ]
    );
})
    ->with([
        ["delete", "backend.roles.destroy"],
        ["put", "backend.roles.update"],
    ]);

it('store role success', function () {
    $this->loggedInAs();

    $data = [
        'name' => 'test new role',
    ];
    post(route('backend.roles.store'), $data, $this->addHeaders());

    assertResponseStatus(201);
    seeJson($data);
});

it('update role success', function () {
    $this->loggedInAs();
    $roleNameTest = 'im role name';

    $role = $this->createRole($roleNameTest);

    $data = [
        'name' => $roleNameTest.' new',
    ];

    put(
        route(
            'backend.roles.update',
            [
                'id' => self::forId($role),
            ]
        ),
        $data,
        $this->addHeaders()
    );

    assertResponseStatus(200);
    seeJson($data);
});

it('update duplicate role', function () {
    $this->loggedInAs();
    $duplicateNameTest = 'im duplicate role name';

    $this->createRole($duplicateNameTest);

    $role = $this->createRole('another role name');

    $data = [
        'name' => $duplicateNameTest,
    ];

    put(
        route(
            'backend.roles.update',
            [
                'id' => self::forId($role),
            ]
        ),
        $data,
        $this->addHeaders()
    );

    assertResponseStatus(422);
    seeJson(
        [
            'message' => "A role `{$duplicateNameTest}` already exists for guard `api`.",
        ]
    );
});

it('store duplicate role', function () {
    $this->loggedInAs();
    $roleNameTest = 'im duplicate role name';

    $this->createRole($roleNameTest);

    $data = [
        'name' => $roleNameTest,
    ];
    post(route('backend.roles.store'), $data, $this->addHeaders());

    assertResponseStatus(422);
    seeJson(
        [
            'message' => "A role `$roleNameTest` already exists for guard `api`.",
        ]
    );
});