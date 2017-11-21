<?php
define('CMD'            , strtoupper($_REQUEST['cmd']));
define('TYPE'           , strtolower($_REQUEST['type']));
define('MODULE'         , strtolower($_REQUEST['module']));
error_reporting( 1 );
header('Content-type: application/json; charset=utf-8');
/*  cmd = get_list & type = masters & module = DAILY & data = {"from_date": "2016-09-04", "offset" : 900}  
     - from_date
    - to_date
    - offset    
    - per_page (100)
    - No. of records
    - */
class API {       
    function __construct(){        
        $class  = TYPE;
        $fn     = MODULE;
        $json   = file_get_contents('php://input');
        $post   = !empty($json) ? json_decode($json) : $_REQUEST;
        $post   = !is_array($post) ? get_object_vars($post) : $post;
        
        require_once './config.php'; //Add with constants and database connections   
        require_once './db/config.php';
        require_once './includes/'. $class . '.php';
        //error_log(CMD.TYPE.MODULE);
        try {            
            if (empty(CMD)) :
            throw new Exception('Command cannot be empty!');
            endif;            
            $class = new $class();
            $class->post = $post;
            $results = $class->$fn();            
        } catch (Exception $exception) {
            $results['header'] = array(
                'status' => 'F',
                'message' => $exception->getMessage()
            );
            $results['data'] = '';
        }        
        if( isset( $post['p'] ) ) :
            print_r( $results );
        else :
            //echo json_encode( $results );
            echo json_encode($results, JSON_UNESCAPED_SLASHES);
        endif;        
        #echo json_encode( $results );
    }        
}
$api = new API();
