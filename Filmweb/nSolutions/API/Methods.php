<?php
/**
* @author Michell Hoduń
* @copyright (c) 2013 nSolutions.pl
* @description Filmweb.pl API
* @version 1.0b
* @link https://github.com/nSolutionsPL/filmweb-api
* @license http://creativecommons.org/licenses/by/3.0/ Creative Commons 3.0
*/
namespace nSolutions\API;
class Methods
{
    protected $method = NULL;
    protected $_args = [];
    protected $method_query, $signature;
    
   /**
    * Konstruktor / Sprawdzamy czy podano wszystkie wymagane argumenty.
    * @param array $args
    * @throws \Exception
    */
    public function __construct($args)
    {
        if(count($this->_args) > count($args))
            throw new \Exception ('Nie podano wszystkich wymaganych argumentów.');

        foreach($this->_args as $k => $v)
        {
            $this->$v = $args[$k];
        }
    }
    
    public function execute()
    {
        $this->prepare();
        return $this->_process();
    }

   /**
    * Przerobienie odpowiedzi z API filmweba.
    * @param object $response
    * @return \stdClass
    */
    protected function parse($response)
    {
        $response = explode("\n", $response);
        $response = json_decode(preg_replace('/ t:[0-9]+/i', '', $response[1]));
        
        $data = new \stdClass;
        foreach($response as $k => $v)
        {
            $key = $this->_response_keys[$k];
            
            if(isset($this->_functions[$key]))
            {
                $function = $this->_functions[$key];
                $data->$key = call_user_func_array($function[0], [$function[1],  $v]);
            }
            else
            {
                $data->$key = $v;
            }
        }
        
        return $data;
    }

   /**
    * Wykonanie requesta do API
    * @return string
    */
    protected function _process()
    {
        $method = '';
        
        foreach($this->methods as $m => $v)
        {
            $method .= $m . ' ['.$v.']\n';
        }
        
        $method_query = $method;
        $signature = $this->_createApiSignature($method);
        
        $response = \nSolutions\Request::execute([
            'methods' => $method_query,
            'signature' => $signature
        ]);
        
        return $this->parse($response);
    }
    
   /**
    * Utworzenie sygnatury
    * @param string $method
    * @return string
    */
    protected function _createApiSignature($method)
    {
        return '1.0,'.md5($method.'android'.\nSolutions\Filmweb::KEY);
    }
}