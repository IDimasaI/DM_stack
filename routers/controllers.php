<?php
class CA {}

class PublicController extends CA {
    public function System($var1) {
       
      include "Pages/System.php";
    }

    public function Regions($var1) {
       
      include "Pages/Regions.php";
    }

    // Код контроллера Public
}