<?php
namespace secure_DM;

    class POST_only {
        private $url_redirect;

        public function __construct($url_redirect) {
            $this->url_redirect = $url_redirect;
            $this->check_POST();
        }

        public function check_POST() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: {$this->url_redirect}");
                exit;
            }
        }
    }


namespace App;

    use Exception;
    use PDO;

    class Database //Подключение к БД
    {
        private static $connection;

        public static function getConnection()
        {
            if (self::$connection === null) {
                $config = require_once 'config/config.php';
                
                try {
                    // Указываем кодировку в DSN
                    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset='.$config['charset'];
                    self::$connection = new PDO($dsn, $config['user'], $config['password']);
                    self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                self::failDB();
                }
            }

            return self::$connection;
        }

        private static function failDB(){
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(['status' => 'error', 'message' => 'Ошибка, БД не найдена']);
            exit;
        }
    }



    class JsonAPI {
        private function setJsonHeader($statusCode) {
            header('Content-Type: application/json');
            http_response_code($statusCode);
        }

        private function exitWithJsonResponse($status, $message, $statusCode) {
            $this->setJsonHeader($statusCode);
            echo json_encode(['status' => $status, 'message' => $message]);
            exit;
        }

        public function success($message, $statusCode = 200) {
            $this->exitWithJsonResponse('success', $message, $statusCode);
        }

        public function fail($message, $statusCode = 200) {
            $this->exitWithJsonResponse('error', $message, $statusCode);
        }


        public function notFoundUser($message, $statusCode = 404) {
            $this->exitWithJsonResponse('Not_Found', $message, $statusCode);
        }

        public function foundUser($message, $statusCode = 200) {
            $this->exitWithJsonResponse('Found', $message, $statusCode);
        }
    }

    class Router {
        private $routes = [];

        public function addRoute(string $pattern, $handler): void {
            if (is_string($handler)) {
                // Если обработчик представляет собой строку, разбиваем на части
                $handler = explode('@', $handler);
            }

            $this->routes[$pattern] = $handler;
        }

        public function handleRequest(string $url): void {
            if ($url === '') {
                return;
            }

            foreach ($this->routes as $pattern => $handler) {
                if (preg_match($pattern, $url, $matches)) {
                    array_shift($matches); // Remove the full match

                    if (is_string($handler)) {
                        $handler = explode('@', $handler);
                    }

                    if (is_array($handler)) {
                        list($class, $method) = $handler;

                        // Проверяем, существует ли метод в классе
                        if (class_exists($class) && method_exists($class, $method)) {
                            $controllerInstance = new $class();
                            call_user_func_array([$controllerInstance, $method], $matches);
                            return;
                        } else {
                            $this->handleNotFound();
                            return;
                        }
                    } elseif (is_callable($handler)) {
                    //если есть функция пример '#^fits/(Перехватчик|Фрегат)$#' => function($type) { include 'routers/fits.php'; }
                        call_user_func_array($handler, $matches);
                        return;
                    } else {
                        $this->handleNotFound();
                        return;
                    }
                }
            }

            $this->handleNotFound();
        }

        private function handleNotFound(): void {
            http_response_code(404);
            echo "<html>
                    <body style='background-color: white;height: 100%;margin: 0;display: flex;align-items: center;justify-content: center;'>
                        <section class='base' id='what?' style=' text-align: center;border: solid #123213 1px;margin-top: 15em;'>
                        <p>Как вы сюда попали ?</p><p>В любом случае, 404 Not Found</p>
                        </section>
                    </body>
                </html>";
        }
    }
   
namespace Session;
    class DmSession
    {
        private $name;

        public function __construct($name)
        {
        
            ini_set("session.cookie_httponly", true);
            $this->name = $name;
        }

        public function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_name($this->name);
            if (!isset($_COOKIE[$this->name])) {
                $randomBytes = bin2hex(random_bytes(32));
                $bigRandom = $randomBytes . session_create_id();
                $_COOKIE[$this->name] = $bigRandom;
            }

            session_id($_COOKIE[$this->name]);
            session_start();
            $_COOKIE[$this->name] = session_id();

            $cookieLifetime = 3600 * 2;

            if (!isset($_COOKIE[$this->name])) {
                session_regenerate_id(true);
                $this->setSessionCookie($cookieLifetime);
            } else {
                $this->setSessionCookie($cookieLifetime);
            }
        }
    }

        private function setSessionCookie($lifetime)
        {
            setcookie(session_name(), session_id(), time() + $lifetime, '/', null, null, true);
        }

        public function stop()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }
    }
?>