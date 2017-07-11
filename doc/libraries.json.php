<?php

require_once(__DIR__.'/../common/code/bootstrap.php');

function libraries_json($params) {
    if (isset($params['version'])) {
        $version = BoostVersion::from($params['version']);
    } else {
        $version = BoostVersion::current();
    }

    $version_libs = array_map(
        function($lib) {
            $r = new BoostLibrary($lib);
            return $r;
        },
        BoostLibraries::load()->get_for_version($version, null,
            'BoostLibraries::filter_all'));

    return BoostLibrary::get_libraries_json($version_libs);
}

header('Content-type: application/json');
try {
    echo libraries_json($_GET);
}
catch (BoostVersion_Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Malformed request', true, 400);
    echo json_encode(Array(
        'error' => $e->getMessage(),
    ));
}
