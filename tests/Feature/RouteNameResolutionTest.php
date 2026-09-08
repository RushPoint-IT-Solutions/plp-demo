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

        $this->assertSame(
            '/registrar/services/reports-admin/loa-reports',
            route('registrar.services.reports-admin.loa-reports', [], false)
        );

        $this->assertSame(
            '/registrar/services/reports-admin/form-137a-monitoring',
            route('registrar.services.reports-admin.form-137a-monitoring', [], false)
        );

        $this->assertSame(
            '/registrar/services/reports-admin/waiver-cancellation-reports',
            route('registrar.services.reports-admin.waiver-cancellation-reports', [], false)
        );

        $this->assertSame(
            '/registrar/registrar-menu/forms/request-form-f-137a/1/request',
            route('registrar.registrar-menu.forms.request-form-f-137a.request', ['student' => 1], false)
        );
    }

    public function test_legacy_short_route_name_is_not_registered()
    {
        $this->expectException(InvalidArgumentException::class);
        route('faculty-create');
    }

    public function test_pwa_manifest_has_required_install_assets()
    {
        $manifestPath = public_path('manifest.webmanifest');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertSame('PLP Portal', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#006837', $manifest['theme_color']);

        $icons = collect($manifest['icons'])->keyBy('sizes');
        $this->assertTrue($icons->has('192x192'));
        $this->assertTrue($icons->has('512x512'));

        foreach (['192x192', '512x512'] as $size) {
            $iconPath = public_path($icons->get($size)['src']);
            $this->assertFileExists($iconPath);
            [$width, $height] = getimagesize($iconPath);
            [$expectedWidth, $expectedHeight] = array_map('intval', explode('x', $size));
            $this->assertSame($expectedWidth, $width);
            $this->assertSame($expectedHeight, $height);
        }
    }

    public function test_service_worker_and_offline_fallback_exist()
    {
        $serviceWorkerPath = public_path('service-worker.js');
        $offlinePath = public_path('offline.html');

        $this->assertFileExists($serviceWorkerPath);
        $this->assertFileExists($offlinePath);

        $serviceWorker = file_get_contents($serviceWorkerPath);
        $this->assertNotFalse(strpos($serviceWorker, "request.mode === 'navigate'"));
        $this->assertNotFalse(strpos($serviceWorker, "request.method !== 'GET'"));
        $this->assertNotFalse(strpos($serviceWorker, 'private media'));
    }
}
