#!/usr/bin/env php
<?php
# Copyright 2017 Daniel James

define('GENEREATE_CSS_USAGE', "
Usage: {}

Generate CSS files for production use.");

require_once(__DIR__.'/../common/code/bootstrap.php');

function main() {
    BoostSiteTools\CommandLineOptions::parse(GENEREATE_CSS_USAGE);

    foreach(glob(BOOST_WEBSITE_DATA_ROOT_DIR."/style-v2/section-*.css") as $path) {
        assert(substr($path, 0, strlen(BOOST_WEBSITE_DATA_ROOT_DIR)) === BOOST_WEBSITE_DATA_ROOT_DIR);
        generate_css_file(substr($path, strlen(BOOST_WEBSITE_DATA_ROOT_DIR)));
    }
}

function generate_css_file($path) {
    $css = load_css($path);
    $hash = md5($css);
    $hashed_path = substr($hash,0,2)."/".substr($hash,2)."/".basename($path);
    $hashed_fs_path = BOOST_WEBSITE_DATA_ROOT_DIR."/generated/css/{$hashed_path}";
    if (!is_dir(dirname($hashed_fs_path))) { mkdir(dirname($hashed_fs_path), 0777, true); }
    file_put_contents($hashed_fs_path, $css);

    $trampoline_path = BOOST_WEBSITE_DATA_ROOT_DIR."/generated/css/".basename($path);
    if (!is_dir(dirname($trampoline_path))) { mkdir(dirname($trampoline_path), 0777, true); }
    file_put_contents($trampoline_path, "@import url({$hashed_path});\n");
}

function load_css($path, $parents = []) {
    assert($path[0] === '/');
    $realpath = realpath(BOOST_WEBSITE_DATA_ROOT_DIR.$path);
    if (!$realpath) { my_error("{$path}: Missing file"); }

    if (array_key_exists($realpath, $parents)) {
        my_error("{$path}: Recursive import");
    }
    $parents[$realpath] = true;

    $css_src = file_get_contents($realpath);
    if ($css_src === false) { my_error("{$path}: Error reading files"); }

    $match_result = preg_match_all('~
            (?P<comment>
                /[*]
                ([^*]|[*](?!/))*
                (?P<comment_end>[*]/[ \t]*\n?)?
            ) |
            (?P<import>
                @import \s* url [(]
                (?P<import_value>[^)]*)
                (?P<import_end>[)]\s*;?[ \t]*\n?)?
            ) |
            (?P<url>
                (?P<url_start>url \s* [(])
                (?P<url_value>[^)]*)
                (?P<url_end>[)])?
            ) |
            (?P<ignore>
                "(?:[^"]|\\\\")*"?
            )
        ~xms', $css_src,
        $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    if (!$match_result) { my_error("{$path}: Parse error"); }

    $result = [];
    $offset = 0;
    foreach ($matches as $match) {
        if ($match[0][1] > $offset) {
            $result[] = substr($css_src, $offset, $match[0][1] - $offset);
        }

        if ($match['comment'][1] != -1) {
            if (empty($match['comment_end'][0])) { my_error("{$path}: Unfinished comment"); }
        }
        else if ($match['import'][1] != -1) {
            if (empty($match['import_end'][0])) { my_error("{$path}: Unfinished import"); }
            $child_url = $match['import_value'][0];

            if (BoostUrl::is_absolute($child_url)) {
                $result[] = $match[0][0];
            } else if (strpbrk($child_url, '?#') !== false)  {
                my_error("{$path}: Invalid import: {$child_url}");
            } else {
                $child_path = BoostUrl::resolve($child_url, $path);
                my_log("{$path}: import {$child_path}");
                $result[] = load_css($child_path, $parents);
            }
        }
        else if ($match['url'][1] != -1) {
            if (empty($match['url_end'][0])) { my_error("{$path}: Unfinished url"); }
            $url = BoostUrl::resolve($match['url_value'][0], $path);

            if (BoostUrl::is_absolute($url)) {
                $result[] = $match[0][0];
            } else {
                my_log("{$path}: rewrite url {$match['url'][0]} to {$url}");
                $result[] = $match['url_start'][0];
                $result[] = "../../../..{$url}";
                $result[] = $match['url_end'][0];
            }
        }
        else if ($match['ignore'][1] != -1) {
            my_log($match[0][0]);
            $result[] = $match[0][0];
        }
        else {
            assert(false);
        }

        $offset = $match[0][1] + strlen($match[0][0]);
    }
    if (strlen($css_src) > $offset) {
        $result[] = substr($css_src, $offset);
    }
    return trim(implode('', $result))."\n";
}

function my_error($message) {
    echo "Error: {$message}\n";
    exit(0);
}

function my_log($message) {
    //echo "LOG: {$message}\n";
}

main();
