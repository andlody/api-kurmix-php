<?php

class api_controller extends Controller
{ 
      function index(){
            $this->write([
                  'message'=>'Kurmix'
            ]);
      }
}