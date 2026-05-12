<?php

namespace Illuminate\Support\Facades;

interface Auth
{
    /**
     * @return \App\Models\Member|false
     */
    public static function loginUsingId(mixed $id, bool $remember = false);

    /**
     * @return \App\Models\Member|false
     */
    public static function onceUsingId(mixed $id);

    /**
     * @return \App\Models\Member|null
     */
    public static function getUser();

    /**
     * @return \App\Models\Member
     */
    public static function authenticate();

    /**
     * @return \App\Models\Member|null
     */
    public static function user();

    /**
     * @return \App\Models\Member|null
     */
    public static function logoutOtherDevices(string $password);

    /**
     * @return \App\Models\Member
     */
    public static function getLastAttempted();
}