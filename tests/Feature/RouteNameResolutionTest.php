<?php

namespace Tests\Feature;

use InvalidArgumentException;
use Tests\TestCase;

class RouteNameResolutionTest extends TestCase
{
    public function test_registrar_phase_routes_are_resolvable()
    {
        $this->assertSame(
            '/registrar/registrar-menu/faculty-management/faculty-create',
            route('registrar.registrar-menu.faculty-mgmt.faculty-create', [], false)
        );

        $this->assertSame(
            '/registrar/registrar-menu/faculty-management/faculty-create',
            route('registrar.registrar-menu.faculty-mgmt.faculty-create.store', [], false)
        );

        $this->assertSame(
            '/registrar/registrar-menu/student-management/student-enrollment',
            route('registrar.registrar-menu.student-mgmt.student-enrollment', [], false)
        );

        $this->assertSame(
            '/registrar/registrar-menu/student-management/student-enrollment',
            route('registrar.registrar-menu.student-mgmt.student-enrollment.store', [], false)
        );
    }

    public function test_legacy_short_route_name_is_not_registered()
    {
        $this->expectException(InvalidArgumentException::class);
        route('faculty-create');
    }
}
