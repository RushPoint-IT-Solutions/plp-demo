<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

class ForcePasswordResetFlowTest extends TestCase
{
    public function test_forced_student_is_redirected_to_password_setup()
    {
        $user = new User([
            'name' => 'Forced Student',
            'username' => 'forced-student',
            'module' => 'student',
            'force_password_reset' => true,
            'student_id' => 1,
        ]);

        $response = $this->actingAs($user)->get('/student');

        $response->assertRedirect(route('password.first_reset'));
    }

    public function test_non_forced_student_can_open_student_portal()
    {
        $user = new User([
            'name' => 'Regular Student',
            'username' => 'regular-student',
            'module' => 'student',
            'force_password_reset' => false,
            'student_id' => 1,
        ]);

        $response = $this->actingAs($user)->get('/student');

        $response->assertStatus(200);
    }
}
