<?php

namespace Volistx\FrameworkKernel\Tests;

use PHPUnit\Framework\Attributes\Test;
use Volistx\FrameworkKernel\Helpers\PermissionsCenter;

class PermissionsCenterTest extends TestCase
{
    #[Test]
    public function test_check_with_matching_permission()
    {
        $key = (object) ['permissions' => ['subscriptions:view']];
        $module = 'subscriptions';
        $operation = 'view';

        $permissionsCenter = new PermissionsCenter();
        $result = $permissionsCenter->check($key, $module, $operation);

        $this->assertTrue($result);
    }

    #[Test]
    public function test_check_with_wildcard_permission()
    {
        $key = (object) ['permissions' => ['*']];
        $module = 'plans';
        $operation = 'create';

        $permissionsCenter = new PermissionsCenter();
        $result = $permissionsCenter->check($key, $module, $operation);

        $this->assertTrue($result);
    }

    #[Test]
    public function test_check_with_no_matching_permission()
    {
        $key = (object) ['permissions' => ['subscriptions:view']];
        $module = 'plans';
        $operation = 'create';

        $permissionsCenter = new PermissionsCenter();
        $result = $permissionsCenter->check($key, $module, $operation);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_check_with_empty_permissions()
    {
        $key = (object) ['permissions' => []];
        $module = 'subscriptions';
        $operation = 'view';

        $permissionsCenter = new PermissionsCenter();
        $result = $permissionsCenter->check($key, $module, $operation);

        $this->assertFalse($result);
    }

    #[Test]
    public function test_get_admin_permissions()
    {
        $expectedResult = [
            '*', // Wildcard permission for all

            'user:*',
            'user:create',
            'user:update',
            'user:delete',
            'user:view',

            'subscriptions:*',
            'subscriptions:create',
            'subscriptions:mutate',
            'subscriptions:delete',
            'subscriptions:view',
            'subscriptions:view-all',
            'subscriptions:logs',
            'subscriptions:stats',
            'subscriptions:cancel',
            'subscriptions:revert-cancel',

            'personal-tokens:*',
            'personal-tokens:create',
            'personal-tokens:update',
            'personal-tokens:delete',
            'personal-tokens:reset',
            'personal-tokens:view',
            'personal-tokens:view-all',
            'personal-tokens:logs',

            'plans:*',
            'plans:create',
            'plans:update',
            'plans:delete',
            'plans:view',
            'plans:view-all',
            'plans:logs',

            'user-logs:*',
            'user-logs:view',
            'user-logs:view-all',

            'admin-logs:*',
            'admin-logs:view',
            'admin-logs:view-all',
        ];

        $permissionsCenter = new PermissionsCenter();
        $result = $permissionsCenter->getAdminPermissions();

        $this->assertEquals($expectedResult, $result);
    }
}
