<?php

use App\Models\Device;
/**
 * @OA\Schema(
 *      title="Store Project request",
 *      description="Store Project request body data",
 *      type="object",
 *      required={"u_id"}
 * )
 */

class RegisterRequest
{
    /**
     * @OA\Property(
     *      title="os",
     *      description="Operating System",
     *      example="android"
     * )
     *
     * @var string
     */
    public $os;

    /**
     * @OA\Property(
     *      title="lang",
     *      description="Language",
     *      example="English"
     * )
     *
     * @var string
     */
    public $lang;

    /**
     * @OA\Property(
     *      title="u_id",
     *      description="Unique Id",
     *      format="int64",
     *      example=1232232
     * )
     *
     * @var integer
     */
    public $u_id;

    /**
     * @OA\Property(
     *      title="app_id",
     *      description="App Id",
     *      format="int64",
     *      example=123223
     * )
     *
     * @var integer
     */
    public $app_id;
}