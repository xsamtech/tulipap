<?php

if (!function_exists("getChannelFromSession")) {
    function getChannelFromSession($session)
    {
        $payload = unserialize(base64_decode($session->payload)); // At this point you have an array

        return $payload['channel'];

        // If we want an object instead, we could typecast it to a stdObject.
        // $payload = (object) unserialize(base64_decode($session->payload));

        // return $payload->channel;
    }
}
