<?php

namespace Cellpass;

class Utils
{
    public static function signURL($url)
    {
        $parsed_url = parse_url($url);

        $scheme = $parsed_url['scheme'];
        $host = $parsed_url['host'];
        $path = $parsed_url['path'];
        $query = $parsed_url['query'];

        $uri = $path . '?' . $query;

        $ts = time();
        $rnd = base_convert(rand(), 10, 36);
        $uri = $uri . '&ts=' . $ts . '&rnd=' . $rnd;
        $sign = md5($uri . API_SECRET);
        $uri = $uri . '&sign=' . $sign;

        return $scheme . '://' . $host . $uri;
    }
}
