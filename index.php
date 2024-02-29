
<?php 
  $jsonData = file_get_contents('asset/asset-manifest.json');
  if ($jsonData === false) {
      die('Error reading asset-manifest.json');
  }
  $package = json_decode($jsonData, true);
  if ($package === null) {
      die('Error decoding JSON');
  }
  $css=$package['file']['css'];
  $js=$package['file']['js'];
?>
<!DOCTYPE html>
<link href="/static/css/<?=$css?>" rel="stylesheet">
<script defer src="/static/js/<?=$js?>"></script>
<meta name="theme-color" content="rgba(255, 255, 255, 0.98)">
<?php 
  include_once 'process/OOP/my_function.php';
  include 'routers/controllers.php';
  use App\Router;
  
  
  $router = new Router();
  
  // Загружаем маршруты из файла collection.php
  
  $collection = include 'routers/collection.php';
  foreach ($collection as $pattern => $handler) {
      $router->addRoute($pattern, $handler);
  }
  
  $url = isset($_GET['url']) ? $_GET['url'] : '';
  if($url===''){include 'Pages/home.php';}
  $router->handleRequest($url);
  ?>


