<?php

/**
 * @OA\Schema(
 *      title="Purchase Request",
 *      description="Purchase Request",
 *      type="object",
 *      required={"client_token"}
 * )
 */

class PurchaseRequest
{
    /**
     * @OA\Property(
     *      title="client_token ",
     *      description="Client Token",
     *      example="2w2ee-665t5-676ty-76bvs"
     * )
     *
     * @var string
     */
    public $client_token ;

    /**
     * @OA\Property(
     *      title="receipt",
     *      description="Hashed Receipt number",
     *      example="2w3e23e2233"
     * )
     *
     * @var string
     */
    public $receipt;
}