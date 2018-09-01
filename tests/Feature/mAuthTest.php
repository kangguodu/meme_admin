<?php

namespace Tests\Feature;

use App\StoreUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use JWTAuth;
use JWTFactory;
use Carbon\Carbon;

class mAuthTest extends TestCase
{
    use DatabaseMigrations; //每次测试后重置数据库
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testNoneParams_422()
    {
        $this->post('api/store/verify_account')
            ->assertStatus(422);
    }

    private function commonUser(){
        $user = factory(\App\StoreUser::class)->create([
            'store_id' => 66,
            'mobile' => 110,
            'zone' => '886'
        ]);
        return $user;
    }

    protected function commonCreateVerification(){
        $verification = factory(\App\Verification::class)->create([
            'zone' => '886',
            'verification_account' => 110,
            'verification_type' => 1,
            'verification_code' => '123456',
            'send_at' => time()
        ]);
    }

    public function testPhoneNotExist_400()
    {
        $user = $this->commonUser();
        $data = [
            'phone' => '120'
        ];
        $this->post('api/store/verify_account',$data)
            ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error_code' => 200006,
            'data' => [],
            'error_msg' => '此帳號不存在'
        ]);
    }

    public function testPhoneExistWhenVerified()
    {
        $user = factory(\App\StoreUser::class)->create([
            'store_id' => 66,
            'mobile' => 110,
            'zone' => '886',
            'password' => bcrypt('hello')
        ]);
        $data = [
            'phone' => '110'
        ];
        $this->post('api/store/verify_account',$data)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 200006,
                'data' => [],
                'error_msg' => '此帳號已驗證'
            ]);
    }

    public function testPhoneExistEmailWhenunVerified()
    {
        $user = factory(\App\StoreUser::class)->create([
            'store_id' => 66,
            'mobile' => 110,
            'zone' => '886',
            'email' => 'test@test.com',
            'email_status' => 'unverified',
            'password' => ''
        ]);
        $data = [
            'phone' => 'test@test.com'
        ];
        $this->post('api/store/verify_account',$data)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 200006,
                'data' => [],
                'error_msg' => '此帳號不存在'
            ]);
    }

    public function testPhoneExistWhenUnverify()
    {
        $user = $this->commonUser();
        $data = [
            'phone' => '110'
        ];
        $this->post('api/store/verify_account',$data)
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'error_code',
                'data' => [
                    'code',
                    'token'
                ],
                'error_msg'
            ]);
    }

    public function testit_422_check_code_fail(){
        $this->post('api/store/check_code')
            ->assertStatus(422);
    }


    protected function tempHeader($phone = '',$code = '')
    {
        $headers = ['Accept' => 'application/json'];

        if (!empty($phone)) {
            $factory = JWTFactory::addClaims([
                'sub'   => env('API_ID'),
                'iss'   => config('app.name'),
                'iat'   => Carbon::now()->timestamp,
                'exp'   => 60 * 5 ,
                'nbf'   => Carbon::now()->timestamp,
                'jti'   => uniqid(),
                'phone' => $phone,
                'code' =>  $code
            ]);

            $payload = $factory->make();

            $token = JWTAuth::encode($payload);
            JWTAuth::setToken($token->get());
            $headers['Authorization'] = 'Bearer '.$token->get();
        }

        return $headers;
    }


    public function testit_400_checkcode_not_exist()
    {
        $user = $this->commonUser();
        $this->commonCreateVerification();
        $this->post('api/store/check_code',[
                'phone' => '110',
                'code' => '1234567'
            ],$this->tempHeader('110','123456'))
            ->assertStatus(400);
    }

    public function testit_200_checkcode_exist()
    {
        $user = $this->commonUser();
        $this->commonCreateVerification();
        $headers = $this->tempHeader('110','123456');
        //\Log::info("header:".json_encode($headers));
        $data = [
            'phone' => '110',
            'code' => '123456'
        ];
        $this->post('api/store/check_code',$data,$headers)
            ->assertStatus(200);
    }

    public function testit_initpw_validate_fail()
    {
        $this->commonUser();
        $data = [
        ];
        $this->post('api/store/init_password',$data)
            ->assertStatus(422);
    }

    public function testInitpwverifyCodeNotExist()
    {
        $user = $this->commonUser();
        $this->commonCreateVerification();
        $headers = $this->tempHeader('111','123456');
        $data = [
            'password' => '123456'
        ];
        $this->post('api/store/init_password',$data,$headers)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => [],
                'error_msg' => '驗證碼不正確',
                'error_code' => 200006
            ]);
    }

    public function testInitpwverifyCodeSuccess()
    {
        $user = $this->commonUser();
        $this->commonCreateVerification();
        $headers = $this->tempHeader('110','123456');
        $data = [
            'password' => '123456'
        ];
        $this->post('api/store/init_password',$data,$headers)
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
                'error_msg' => '',
                'error_code' => 0
            ]);
    }

}
