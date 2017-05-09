<?php

require_once(__DIR__.'/../common/code/bootstrap.php');

session_name('boost-commitbot');
session_start();

class BoostCommitBotApp {
    var $protocol;
    var $method;
    var $request_path;

    var $base_url;
    var $base_path;
    var $path;
    var $params;

    static function go() {
        $is_404 = false;

        $page = new BoostCommitBotApp;
        $page->protocol = 'HTTP/1.1';
        $page->method = strtolower(BoostWebsite::array_get($_SERVER, 'REQUEST_METHOD'));
        $page->base_url = BOOST_BOT_SERVER_NAME."bot/";
        $page->base_path = "/bot/";

        $path_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $page->request_path = $path_parts[0];
        if (preg_match('@^/+bot/+(\w*)/*$@', $page->request_path, $match)) {
            $page->path = $match[1];
        } else {
            $page->path = '((404))';
            return $page->error_page(404, "File Not Found");
        }
        $page->params = $_GET ?: null;

        return $page->main();
    }

    function main() {
        switch ($this->method) {
        case 'get':
        case 'post':
            break;
        case 'head':
            $this->method = 'get';
            break;
        default:
            header('Allow', 'GET, HEAD, POST');
            return $this->error_page(405, 'Method Not Allowed');
        }

        $routes = array(
            '' => 'HomePage',
            'login' => 'LoginPage',
            'logout' => 'LogoutPage',
            'callback' => 'CallbackPage',
        );

        $route_class = BoostWebsite::array_get($routes, $this->path);
        $route_handler = $route_class ? new $route_class() : null;

        $is_public = $route_handler &&
            method_exists($route_handler, 'is_public') &&
            $route_handler->is_public($this);

        $redirect_if_appropriate = true;

        if (!$this->logged_in_user() && !$is_public) {
            $route_class = 'AutoLoginPage';
            $route_handler = new AutoLoginPage();
            $redirect_if_appropriate = false;
            $is_public = true;
        }
        else if ($this->method == 'post') {
            $redirect_if_appropriate = false;
        }

        if (!$route_class) {
            return $this->error_page(404, 'File Not Found');
        }

        if ($redirect_if_appropriate && "{$this->full_path($this->path)}" != $this->request_path) {
            $this->redirect($this->path, $this->params);
            return;
        }

        switch ($this->method) {
        case 'get':
            if (method_exists($route_handler, 'get')) {
                return $route_handler->get($this);
            } else {
                return $this->error_page(404, 'File Not Found');
            }
        case 'post':
            if (method_exists($route_handler, 'post')) {
                return $route_handler->post($this);
            } else {
                return $this->error_page(404, 'File Not Found');
            }
        default:
            assert(false);
        }
    }

    function full_path($path) {
        return "{$this->base_path}{$path}";
    }

    function full_url($path, $params = null) {
        $url = "{$this->base_url}{$path}";
        if ($params) {
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= http_build_query($params);
        }
        return $url;
    }

    function path_plus_params($path, $params = null) {
        if ($params) {
            $path .= '?';
            $path .= http_build_query($params);
        }
        return $path;
    }

    // HTML/HTTP Responses

    function redirect($path, $params = null) {
        header("Location: {$this->full_url($path, $params)}");
    }

    function html_page($title, $content_html) {
        // Taken from: https://code.jquery.com/
        $jquery_javascript_html = '<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>';
        // Taken from: http://getbootstrap.com/getting-started/
        $bootstrap_stylesheet_html = '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">';
        $bootstrap_javascript_html = '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>';

        $title_html = htmlentities(trim($title));
        $content_html = trim($content_html);

        $result = '';
        $result .= "<!DOCTYPE html>\n";
        $result .= "<html>\n";
        $result .= "<head>\n";
        $result .= "{$bootstrap_stylesheet_html}\n";
        $result .= "<title>{$title_html}</title>\n";
        $result .= "</head>\n";
        $result .= "<body>\n";
        $result .= "<nav class='navbar navbar-default'>\n";
        $result .= "<div class='container-fluid'>\n";

        //
        $result .= "<div class='navbar-header'>\n";
        $result .= "<button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-example-navbar-collapse-1' aria-expanded='false'>\n";
        $result .= "<span class='sr-only'>Toggle navigation</span>\n";
        $result .= "<span class='icon-bar'></span>\n";
        $result .= "<span class='icon-bar'></span>\n";
        $result .= "<span class='icon-bar'></span>\n";
        $result .= "</button>\n";
        $result .= "<a class='navbar-brand' href='#'>Boost Commitbot</a>\n";
        $result .= "</div>\n"; // .navbar-header

        // TODO: Generate a proper menu once there are more pages.
        $result .= "<div class='collapse navbar-collapse' id='bs-example-navbar-collapse-1'>\n";
        $result .= "<ul class='nav navbar-nav'>\n";
        $result .= "<li class='active'><a href='#'>Dashboad <span class='sr-only'>(current)</span></a></li>\n";
        $result .= "</ul>\n"; // .nav
        $result .= "</div>\n"; // #bs-example-navbar-collapse-1

        $result .= "</div>\n"; // .container-fluid
        $result .= "</nav>\n";

        $result .= "<div class=container>\n";
        $result .= "<h1>{$title_html}</h1>\n";
        $result .= "{$content_html}\n";
        $result .= "</div>\n"; // .container

        $result .= "{$jquery_javascript_html}\n";
        $result .= "{$bootstrap_javascript_html}\n";
        $result .= "</body>\n";
        $result .= "</html>\n";

        return $result;
    }

    // TODO: 404 error page should show if user is logged in.
    function error_page($code, $description) {
        $title_html = htmlentities("{$code} {$description}");
        header("{this->protocol} {$code} {$description}");
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        echo "<title>{$title_html}</title>\n";
        echo "</head>\n";
        echo "<body>\n";
        echo "<h1>{$title_html}</h1>\n";
        echo "</body>\n";
        echo "</html>\n";
    }

    // Auth state

    function logged_in_user() {
        return isset($_SESSION) ? BoostWebsite::array_get($_SESSION, 'github_name') : null;
    }
}

class HomePage {
    function get($site) {
        echo $site->html_page("Boost Commitbot",
            BoostSimpleTemplate::render(
                'Logged in as: {{username}} (<a href="{{logout_url}}">log out</a>)',
                array(
                    'username' => $site->logged_in_user(),
                    'logout_url' => $site->full_path('logout'),
                )));
    }
}

class AutoLoginPage {
    function is_public($site) { return true; }

    function get($site) {
        echo $site->html_page("Login",
            BoostSimpleTemplate::render(
                '<form method=POST action="{{login_url}}">'.
                '<input type=hidden name=path value="{{path}}">'.
                '<button type=submit name=action value=github-login class="btn btn-default">Login with GitHub</a>'.
                '</form>',
                array(
                    'path' => $site->path_plus_params($site->path, $site->params),
                    'login_url' => $site->full_url('login'),
                )));

    }
}

class LoginPage extends AutoLoginPage {
    function post($site) {
        $submit_action = BoostWebsite::array_get($_POST, 'action');
        if ($submit_action != 'github-login') {
            return $site->error_page(400, "Bad request");
        }

        $state = bin2hex(openssl_random_pseudo_bytes(16));
        $path_plus_params = BoostWebsite::array_get($_POST, 'path');
        $_SESSION['github_login_state'] = $state;
        $_SESSION['login_path'] = $path_plus_params;
        $_SESSION['login_time'] = time();
        GitHub::redirect_to_github_login($site->full_url('callback'), $state);
    }
}

class LogoutPage {
    function is_public($site) { return true; }

    function get($site) {
        $session_name = 'boost-commitbot';
        session_unset();
        session_destroy();
        setcookie($session_name, '', time() - 3600, '/');
        $site->redirect("");
    }
}

class CallbackPage {
    function is_public($site) { return true; }

    function get($site) {
        $session_state = BoostWebsite::array_get($_SESSION, 'github_login_state');
        $path_plus_params = BoostWebsite::array_get($_SESSION, 'login_path');
        $login_time = BoostWebsite::array_get($_SESSION, 'login_time');
        if (empty($session_state)) {
            return $this->login_error("No state in session");
        }

        if (time() - $login_time > 60*60) {
            return $this->login_error("Login request has expired");
        }

        $github_state = BoostWebsite::array_get($_GET, 'state', '');
        if ($github_state != $session_state) {
            return $this->login_error("Error confirming login with git: State doesn't match session");
        }

        try {
            $username = GitHub::github_callback($github_state);
        } catch (GitHub_Error $e) {
            return $this->login_error("Error confirming login with git: {$e}");
        }

        unset($_SESSION['github_login_state']);
        unset($_SESSION['login_path']);
        $_SESSION['github_name'] = $username;

        if (preg_match('@^\w+(?:[?].*)$@', $path_plus_params)) {
            $site->redirect($path_plus_params);
        } else {
            $site->redirect('');
        }
    }

    // TODO: Better error messages.
    function login_error($message) {
        echo BoostSimpleTemplate::render(
            "Error logging in with git: {{message}}",
            array(
                'message' => $message,
            ));
    }
}

class GitHub {
    static function redirect_to_github_login($redirect_uri, $state) {
        $client_id = BOOST_BOT_GITHUB_CLIENT_ID;
        assert(!!$client_id);
        $github_login_url = "https://github.com/login/oauth/authorize?".
            http_build_query(array(
                'client_id' => $client_id,
                'state' => $state,
                'redirect_uri' => $redirect_uri));

        header("Location: {$github_login_url}");
    }

    static function github_callback($state) {
        $params = array(
            'client_id' => BOOST_BOT_GITHUB_CLIENT_ID,
            'client_secret' => BOOST_BOT_GITHUB_CLIENT_SECRET,
            'code' => BoostWebsite::array_get($_GET, 'code'),
            'state' => $state,
        );
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($params),
            )));
        // TODO: Store warnings for error message.
        $result = file_get_contents('https://github.com/login/oauth/access_token', false, $context);
        if ($result === false) {
            throw new Github_Error("Error checking authentication");
        }
        $response_vars = null;
        parse_str($result, $response_vars);
        if (array_key_exists('error', $response_vars)) {
            throw new Github_Error("Error checking authentication: ".
                BoostWebsite::array_get($response_vars, 'error_description',
                    BoostWebsite::array_get($response_vars, 'error')));
        }

        $github_access_token = BoostWebsite::array_get($response_vars, 'access_token');
        if (empty($github_access_token)) {
            throw new Github_Error("Unable to parse response from GitHub.\n");
        }

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => "Accept: application/vnd.github.v3+json\r\nAuthorization: token {$github_access_token}\r\nUser-Agent: Boost Bot\r\n",
            )));
        // TODO: Again, store warnings for error message.
        $result = file_get_contents('https://api.github.com/user', false, $context);
        if ($result === false) {
            throw new Github_Error("Error getting username");
        }

        $user_details = json_decode($result, true);
        $username = $user_details ? BoostWebsite::array_get($user_details, 'login') : null;
        if (empty($username)) {
            throw new Github_Error("Error getting username");
        }

        return $username;
    }
}

class GitHub_Error extends RuntimeException {}

BoostCommitBotApp::go();
