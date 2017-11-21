<?php
class users extends DB {

    function __construct() {
        parent::__construct();
    }

    /*
     * USERS
     */

    function lists() {
        switch (CMD) :
            case 'LOGIN' :
            // Get POST Details
                $data = (isset($this->post['data'])) ? (array) json_decode($this->post['data']) : array();
                //check conditions
            ..
            default :
                throw new Exception('Given command is not found!');
                break;
        endswitch;
    }
    ...
    ...
    }
    ?>
