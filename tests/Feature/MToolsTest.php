<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MToolsTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSendSmsNotHavePhone()
    {
        $this->post('/api/common/sendsms')
            ->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'error_code',
                'data',
                'error_msg'
            ]);
    }

    public function testSendSms()
    {
        $data = [
            'phone' => '110'
        ];
        $this->post('/api/common/sendsms',$data)
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'error_code',
                'data' => [
                    'code'
                ],
                'error_msg',
            ]);
    }
}
