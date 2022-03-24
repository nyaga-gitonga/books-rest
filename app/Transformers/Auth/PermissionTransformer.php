<?php
/**
 * Created by PhpStorm.
 * User: Lloric Mayuga Garcia <lloricode@gmail.com>
 * Date: 11/24/18
 * Time: 3:31 PM
 */

namespace App\Transformers\Auth;

use App\Models\Auth\Permission\Permission;
use App\Transformers\BaseTransformer;

class PermissionTransformer extends BaseTransformer
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
    ];
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
    ];

    /**
     * A Fractal transformer.
     *
     * @param  \App\Models\Auth\Permission\Permission  $permission
     *
     * @return array
     */
    public function transform(Permission $permission)
    {
        return [
            'id' => self::forId($permission),
            'name' => $permission->name,
        ];
    }

    /**
     * @return string
     */
    public function getResourceKey(): string
    {
        return 'permissions';
    }
}
