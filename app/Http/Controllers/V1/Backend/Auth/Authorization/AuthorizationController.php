<?php

namespace App\Http\Controllers\V1\Backend\Auth\Authorization;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\Permission\PermissionRepository;
use App\Repositories\Auth\Role\RoleRepository;
use App\Repositories\Auth\User\UserRepository;
use App\Transformers\Auth\RoleTransformer;
use App\Transformers\Auth\UserTransformer;
use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
    protected UserRepository $userRepository;
    protected RoleRepository $roleRepository;

    public function __construct(UserRepository $userRepository, RoleRepository $roleRepository)
    {
        $this->middleware('permission:'.config('setting.permission.permission_names.manage_authorization'));

        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     *
     * @OA\Post(
     *     path="/auth/authorizations/assign-role-to-user",
     *     summary="Assign role to user",
     *     tags={"Authorization"},
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 enum = {"roles", "permissions"},
     *             )
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="User key",
     *                     property="user_id",
     *                     type="int",
     *                 ),
     *                 @OA\Property(
     *                     description="Role key",
     *                     property="role_id",
     *                     type="int",
     *                 ),
     *                 example={
     *                  "user_id": "user-at-usercom",
     *                   "role_id": 1
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserTransformer")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Error")
     *         ),
     *     ),
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Spatie\Fractal\Fractal
     * @throws \Illuminate\Validation\ValidationException
     * @api                {post} /auth/authorizations/assign-role-to-user Assign role to user
     * @apiName            assign-role-to-user
     * @apiGroup           Authorization
     * @apiVersion         1.0.0
     * @apiPermission      Authenticated User
     * @apiUse             UserResponse
     * @apiParam {String} user_id User hashed id
     * @apiParam {String} role_id Role hashed id
     *
     */
    public function assignRoleToUser(Request $request)
    {
        $attributes = $this->validate(
            $request,
            [
                'user_id' => $this->userRules(),
                'role_id' => $this->roleRules(),
            ]
        );

        $this->userRepository->assignRole($attributes['user_id'], $attributes['role_id']);

        return $this->fractal($this->userRepository->findByRouteKeyName($attributes['user_id']), new UserTransformer());
    }

    /**
     * @OA\Delete(
     *     path="/auth/authorizations/revoke-role-from-user",
     *     summary="Revoke role from user",
     *     tags={"Authorization"},
     *     security={{"passport":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="User key",
     *                     property="user_id",
     *                     type="int",
     *                 ),
     *                 @OA\Property(
     *                     description="Role keyd",
     *                     property="role_id",
     *                     type="int",
     *                 ),
     *                 example={
     *                  "user_id": "user-at-usercom",
     *                   "role_id": 1
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="The resource was revoked successfully.",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Error")
     *         ),
     *     ),
     * )
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @api                {delete} /auth/authorizations/revoke-role-from-user Revoke role form user
     * @apiName            revoke-role-from-user
     * @apiGroup           Authorization
     * @apiVersion         1.0.0
     * @apiPermission      Authenticated User
     * @apiUse             UserResponse
     * @apiParam {String} user_id User hashed id
     * @apiParam {String} role_id Role hashed id
     *
     */
    public function revokeRoleFormUser(Request $request)
    {
        $attributes = $this->validate(
            $request,
            [
                'user_id' => $this->userRules(),
                'role_id' => $this->roleRules(),
            ]
        );

        $this->userRepository->removeRole($attributes['user_id'], $attributes['role_id']);

        return response('', 204);
    }

    /**
     * @OA\Post(
     *     path="/auth/authorizations/assign-permission-to-user",
     *     summary="Assign permission to user",
     *     tags={"Authorization"},
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 enum = {"roles", "permissions"},
     *             )
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="User key",
     *                     property="user_id",
     *                     type="int",
     *                 ),
     *                 @OA\Property(
     *                     description="Permission key",
     *                     property="permission_id",
     *                     type="int",
     *                 ),
     *                 example={
     *                  "user_id": "user-at-usercom",
     *                   "permission_id": 1
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserTransformer")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Error")
     *         ),
     *     ),
     * )
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Spatie\Fractal\Fractal
     * @throws \Illuminate\Validation\ValidationException
     * @api                {post} /auth/authorizations/assign-permission-to-user Assign permission to user
     * @apiName            assign-permission-to-user
     * @apiGroup           Authorization
     * @apiVersion         1.0.0
     * @apiPermission      Authenticated User
     * @apiUse             UserResponse
     * @apiParam {String} user_id User hashed id
     * @apiParam {String} permission_id Permission hashed id
     *
     */
    public function assignPermissionToUser(Request $request)
    {
        $attributes = $this->validate(
            $request,
            [
                'user_id' => $this->userRules(),
                'permission_id' => $this->permissionRules(),
            ]
        );

        $this->userRepository->givePermissionTo($attributes['user_id'], $attributes['permission_id']);

        return $this->fractal($this->userRepository->findByRouteKeyName($attributes['user_id']), new UserTransformer());
    }

    /**
     * @OA\Delete(
     *     path="/auth/authorizations/revoke-permission-from-user",
     *     summary="Revoke permission from user",
     *     tags={"Authorization"},
     *     security={{"passport":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="User key",
     *                     property="user_id",
     *                     type="int",
     *                 ),
     *                 @OA\Property(
     *                     description="Permission key",
     *                     property="permission_id",
     *                     type="int",
     *                 ),
     *                 example={
     *                  "user_id": "user-at-usercom",
     *                   "permission_id": 1
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="The resource was revoked successfully.",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Error")
     *         ),
     *     ),
     * )
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @api                {delete} /auth/authorizations/revoke-permission-from-user Revoke permission from user
     * @apiName            revoke-permission-from-user
     * @apiGroup           Authorization
     * @apiVersion         1.0.0
     * @apiPermission      Authenticated User
     * @apiUse             UserResponse
     * @apiParam {String} user_id User hashed id
     * @apiParam {String} permission_id Permission hashed id
     *
     */
    public function revokePermissionFromUser(Request $request)
    {
        $attributes = $this->validate(
            $request,
            [
                'user_id' => $this->userRules(),
                'permission_id' => $this->permissionRules(),
            ]
        );

        $this->userRepository->revokePermissionTo($attributes['user_id'], $attributes['permission_id']);

        return response('', 204);
    }

    /**
     * @OA\Post(
     *     path="/auth/authorizations/attach-permission-to-role",
     *     summary="Attach permission to role",
     *     tags={"Authorization"},
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 enum = {"roles", "permissions"},
     *             )
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Role key",
     *                     property="role_id",
     *                     type="int",
     *                 ),
     *                 @OA\Property(
     *                     description="Permission key",
     *                     property="permission_id",
     *                     type="int",
     *                 ),
     *                 example={
     *                  "role_id": 1,
     *                   "permission_id": 1
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserTransformer")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Error")
     *         ),
     *     ),
     * )
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Spatie\Fractal\Fractal
     * @throws \Illuminate\Validation\ValidationException
     * @api                {post} /auth/authorizations/attach-permission-to-role Attach permission to role
     * @apiName            attach-permission-to-role
     * @apiGroup           Authorization
     * @apiVersion         1.0.0
     * @apiPermission      Authenticated User
     * @apiUse             RoleResponse
     * @apiParam {String} role_id Role hashed id
     * @apiParam {String} permission_id Permission hashed id
     *
     */
    public function attachPermissionToRole(Request $request)
    {
        $attributes = $this->validate(
            $request,
            [
                'role_id' => $this->roleRules(),
                'permission_id' => $this->permissionRules(),
            ]
        );

        $this->roleRepository->givePermissionTo($attributes['role_id'], $attributes['permission_id']);

        return $this->fractal($this->roleRepository->findByRouteKeyName($attributes['role_id']), new RoleTransformer());
    }

    /**
     * @OA\Delete(
     *     path="/auth/authorizations/revoke-permission-from-role",
     *     summary="Revoke permission from role",
     *     tags={"Authorization"},
     *     security={{"passport":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Role key",
     *                     property="role_id",
     *                     type="int",
     *                 ),
     *                 @OA\Property(
     *                     description="Permission key",
     *                     property="permission_id",
     *                     type="int",
     *                 ),
     *                 example={
     *                  "role_id": 1,
     *                   "permission_id": 1
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="The resource was revoked successfully.",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Error")
     *         ),
     *     ),
     * )
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @api                {delete} /auth/authorizations/revoke-permission-from-role Revoke permission from role
     * @apiName            revoke-permission-from-role
     * @apiGroup           Authorization
     * @apiVersion         1.0.0
     * @apiPermission      Authenticated User
     * @apiUse             RoleResponse
     * @apiParam {String} role_id Role hashed id
     * @apiParam {String} permission_id Permission hashed id
     *
     */
    public function revokePermissionFromRole(Request $request)
    {
        $attributes = $this->validate(
            $request,
            [
                'role_id' => $this->roleRules(),
                'permission_id' => $this->permissionRules(),
            ]
        );

        $this->roleRepository->revokePermissionTo($attributes['role_id'], $attributes['permission_id']);

        return response('', 204);
    }


    private function roleRules(): string
    {
        return sprintf(
            'required|exists:%s,%s',
            $this->roleRepository->model(),
            $this->roleRepository->getRouteKeyName()
        );
    }

    private function userRules(): string
    {
        return sprintf(
            'required|exists:%s,%s',
            $this->userRepository->model(),
            $this->userRepository->getRouteKeyName()
        );
    }

    private function permissionRules(): string
    {
        $repo = app(PermissionRepository::class);
        return sprintf('required|exists:%s,%s', $repo->model(), $repo->getRouteKeyName());
    }
}