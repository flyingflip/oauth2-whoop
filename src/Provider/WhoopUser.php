<?php

namespace FlyingFlip\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class WhoopUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $userInfo = [];

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->userInfo = $response;
    }

    public function getId()
    {
        return $this->userInfo['user_id'];
    }

    /**
     * Get the display name.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->userInfo['first_name'] . ' ' . $this->userInfo['last_name'];
    }

    public function getEmail()
    {
        return $this->userInfo['email'];
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->userInfo;
    }
}
