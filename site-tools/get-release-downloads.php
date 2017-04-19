#!/usr/bin/env php
<?php
require_once(__DIR__.'/../common/code/boost.php');

use GetOptionKit\OptionCollection;
use BoostTasks\BinTrayCache;
use BoostSiteTools\CommandLineOptions;

define('GET_RELEASE_DOWNLOAD_USAGE', "
Usage: {} bintray_url

Downloads the release details from bintray. Example URLs:

https://dl.bintray.com/boostorg/beta/1.64.0.rc.1/
https://dl.bintray.com/boostorg/beta/1.64.0.beta.1/
https://dl.bintray.com/boostorg/release/1.64.0/

");

function main($args) {
    $options = CommandLineOptions::parse(
        GET_RELEASE_DOWNLOAD_USAGE, array('quiet' => false));

    $url = null;

    switch (count($options->positional)) {
    case 1:
        $url = $options->positional[0];
        break;
    default:
        echo $options->usage_message();
        exit(1);
    }

    if (!preg_match(
        '@^https?://dl.bintray.com/boostorg/(beta|release)/([^/]+)/(?:source|binaries/?)?$@',
        $url, $match)) {
        echo "Unable to interpret URL: {$url}\n";
        exit(1);
    }

    $category = $match[1];
    $bintray_version = $match[2];

    // Strip release candidate details as BoostVersion doesn't understand them.
    $is_release_candidate = preg_match("@^(.*)[.]rc[.]?\d*$@", $bintray_version, $match);
    $version2 = $is_release_candidate ? $match[1] : $bintray_version;

    try {
        $version_object = BoostVersion::from($version2);
    } catch (BoostVersion_Exception $e) {
        echo "Failed to interpret version {$bintray_version}\n";
        exit(1);
    }

    $bintray_api_url = "https://api.bintray.com/packages/boostorg/{$category}/boost/files";

    $details = file_get_contents($bintray_api_url);
    if ($details === false) {
        echo "Error downloading details from bintray API\n";
        exit(1);
    }

    $details = json_decode($details);
    if (is_null($details)) {
        echo "Error decoding json from bintray\n";
        exit(1);
    }

    $extensions = array(
        'bz2' => 'unix',
        'gz' => 'unix',
        'zip' => 'windows',
        '7z' => 'windows',
    );

    $path_matcher = '@^'.preg_quote($bintray_version, '@').'/(binaries|source)/([^/]*)$@';
    $downloads = array();
    foreach($details as $x) {
        if (preg_match($path_matcher, $x->path, $match)) {
            $section = $match[1];
            $filename = $match[2];
            switch ($section) {
            case 'source':
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                if (array_key_exists($extension, $extensions)) {
                    $downloads[$extension] = array(
                        'line_endings' => $extensions[$extension],
                        'url' => "https://dl.bintray.com/boostorg/{$category}/{$x->path}",
                        'sha256' => $x->sha256
                    );
                }
                break;
            case 'binaries':
                // TODO
                break;
            default:
                assert(false);
            }
        }
    }

    if (!$downloads) {
        echo "Didn't find any downloads on Bintray.\n";
        exit(1);
    }

    $releases = new BoostReleases(__DIR__.'/../generated/state/release.txt');
    $download_page = "https://dl.bintray.com/boostorg/{$category}/{$bintray_version}/source/";
    $releases->set_release_data('boost', $version_object, array(
        'download_page' => $download_page,
        'downloads' => $downloads,
    ));
    $releases->save();

    if (!$is_release_candidate) {
        // TODO: Mark as released. (set-release-status.php)
        // TODO: Download from bintray, and extract into place (sort of like
        //       update-website-documentation in boost-tasks)
        // TODO: Update documentation location (scan-documentation.php)
        // TODO: Update doc list from release (update-doc-list.php).
        // TODO: Update 'latest' in doc list (or remove need for it?)
        // TODO: Regenerate pages (update-pages.php).

        // TODO: Reuse download if already preseent - might be a release
        //       candidate, so maybe not. Or store its details so that
        //       they can be compared later.
    }
}

main($_SERVER['argv']);
