<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});
<<<<<<< HEAD
$factory->define(App\Store::class, function (Faker\Generator $faker) {
    static $id;
    static $super_uid;
    static $name;
    static $city;
    static $district;
    static $address;
    static $code;

    /**
     * @property string $mobile
     */
    $data = [
        'id' => $id,
        'super_uid' => $super_uid,
        'name' => $name,
        'city' => $city,
        'district' => $district,
        'address' => $address,
        'code' => $code
    ];
    return $data;
});
$factory->define(App\StoreUser::class, function (Faker\Generator $faker) {
    static $password;
    static $mobile;
    static $store_id;
    static $zone;
    /**
     * @property string $mobile
     */
    $data = [
        'mobile' => $mobile,
        'store_id' => $store_id,
        'zone' => $zone,
        'email' => $faker->unique()->safeEmail
    ];
    if($password != ''){
        $data['password'] = $password;
    }
    return $data;
});

$factory->define(App\Verification::class, function (Faker\Generator $faker) {
    static $verification_account;
    static $verification_type;
    static $verification_code;
    static $send_at;
    static $zone;

    $data = [
        'zone' => $zone?$zone:'',
        'verification_account' => $verification_account,
        'verification_type' => $verification_type?$verification_type:1,
        'verification_code' => $verification_code,
        'send_at' => $send_at
    ];
    return $data;
});
=======
>>>>>>> 7bbfd9d173437189162bdf1f44601fbffa2b3d7a
