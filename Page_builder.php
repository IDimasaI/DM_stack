<?php
$filesJson = json_decode(file_get_contents('Pages.config.json', true), true);

$folder = 'Pages';

function create_file($folder)
{
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
        echo "Папка успешно создана.\n";
    } else {
        echo "Папка уже существует.\n";
    }
}

create_file($folder);

$controllerContent = file_get_contents("routers/controllers.php");
function malware($controller, $vars, $filesJson){
    $output = $vars;
    if(isset($vars) && empty($vars)) {
        $output = '123'; // если переменная пуста, присваиваем значение '123'
    }
    $func=$filesJson['controller-function'][$controller];
   
    return "$func";
}

foreach ($filesJson['Pages'] as $router) {
    $fileName = $router['name_file'];
    if ($filesJson['Page-template'] == 'base' && (!isset($router['pattern']) || empty($router['pattern']))) { // Шаблон содержимого в файле страницы
        $fileContent = '<head><title>Страница в разработке</title></head><body><p class="text-center" style="margin-top: 3em;">Страницы еще не существует, вернитесь позже.</p></body>';
    } elseif ($router['pattern']) {
        if(file_exists($router['pattern'])){
         $fileContent = file_get_contents($router['pattern']);
        }else{$fileContent=$router['pattern'];}
    } else {
        if(file_exists($filesJson['Page-template'])){
            $fileContent = file_get_contents($filesJson['Page-template']);
        }else{$fileContent=$filesJson['Page-template'];}
    }
    
    if (!file_exists("$folder/$fileName")) {
        file_put_contents("$folder/$fileName", $fileContent);
        echo "Файл $fileName успешно создан.\n";
    } else {
        echo "Файл $fileName уже есть.\n";
    }

    $functionName = $router['name_func'];
    $controllerType = $router['controller'];
    $nameController=$controllerType."Controller";
    $functionExists = false;

    $validationUrl = $router['url'];
    $slashCount = substr_count($validationUrl, '/');
    
    $vars = "";
    for ($i = 1; $i <= $slashCount; $i++) {
        $vars .= "\$var" . $i . ",";
    }
    $vars = rtrim($vars, ",");

    // Проверка наличия функции в контроллере
    $functionExists = false;
    $existingFunctionPattern = "/public function $functionName\((.*?)\)/";
    preg_match($existingFunctionPattern, $controllerContent, $existingFunctionMatches);
    
    if (!empty($existingFunctionMatches)) {
        $existingFunctionParams = $existingFunctionMatches[1];
        if ($existingFunctionParams !== $vars) {
            // Удаление существующей функции из контроллера
            $controllerContent = preg_replace("/public function $functionName\((.*?)\)\s*{.*?}\n/s", '', $controllerContent);
            echo "Функция $functionName с другим количеством параметров была удалена из контроллера.\n";
        } else {
            $functionExists = true;
            echo "Функция $functionName\n";
        }
    }

    // Создание контроллера, если он не существует
    if (strpos($controllerContent, "class $nameController") === false) {
        $controllerContent .= "\n\nclass $nameController extends CA {\n    // Код контроллера $controllerType\n}";
        echo "Контроллер $controllerType успешно создан и добавлен в файл.\n";
    }

    // Добавление функции в контроллер
    $existingFunctionPattern = "/public function $functionName\((.*?)\)\s*{.*?}\n/s";
    preg_match($existingFunctionPattern, $controllerContent, $existingFunctionMatches);

    if (!$functionExists) { 
        $functionContent = "    public function $functionName($vars) {\n       ".malware($controllerType, $vars, $filesJson)."\n      include \"$folder/$fileName\";\n    }\n";
        $controllerContent = preg_replace("/class $nameController extends CA \{/s", "class $nameController extends CA {\n$functionContent", $controllerContent, 1);
        echo "Функция $functionName успешно добавлена в контроллер $controllerType.\n";
    } else {
        $existingFunctionContent = $existingFunctionMatches[0];
        $newFunctionContent = "public function $functionName($vars) {\n       ".malware($controllerType, $vars, $filesJson)."\n      include \"$folder/$fileName\";\n    }\n";

        // Проверка отличий в файлах
        if ($existingFunctionContent !== $newFunctionContent) {
            $controllerContent = str_replace($existingFunctionContent, $newFunctionContent, $controllerContent);
            echo "Данные для функции $functionName успешно обновлены в контроллере.\n";
        } else {
            echo "Функция $functionName в контроллере уже содержит такие же данные. Обновление не требуется.\n";
        }
    }
}
if(isset($filesJson['include-in-controller'])&&$filesJson['include-in-controller'] !=="none"){
    if(file_exists($filesJson['include-in-controller'])){
        $include_file = file_get_contents($filesJson['include-in-controller']);
    }else{$include_file=$filesJson['include-in-controller'];}
}

if (strpos($controllerContent, "<?php") === false) {
    $controllerContent = "<?php\n{$include_file}\nclass CA {}" . $controllerContent . "";
}

file_put_contents("routers/controllers.php", $controllerContent);

echo "Страницы созданы!\n";

function collection(){
    $pages = json_decode(file_get_contents('Pages.config.json'), true)['Pages'];

    $routes = [];

    foreach ($pages as $page) {
        $url = $page['url'];
        $controller = $page['controller'];
        $function = $page['name_func'];

        $pattern = "#^{$url}$#";
        $route = "{$controller}Controller@{$function}";

        $routes[$pattern] = $route;
    }

    $fileContent = "<?php return [\n";
    foreach ($routes as $pattern => $route) {
        $fileContent .= "    '{$pattern}' => '{$route}',\n";
    }
    $fileContent .= "];\n?>";

    file_put_contents('routers/collection.php', $fileContent, LOCK_EX);

    echo "Пути перезаписаны !\n";
}
collection();
?>