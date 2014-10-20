<?php

namespace Vectorface\SnappyRouter;

function function_exists($class)
{
    $blacklist = array(
        '\http_response_code' => 0
    );

    if (in_array($class, $blacklist)) {
        return false;
    } else {
        return \function_exists($class);
    }
}
