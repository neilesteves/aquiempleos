<?php

class Mongo_Aviso extends Mongo_Collection
{
    /**
     * @var MongoCollection
     */
    protected $_collection = 'aviso';

    public function __construct()
    {
        parent::__construct();
        $this->setUpCollection($this->_collection);
    }

    public function save($datos)
    {
        $datos['fecha_hora'] = date('Y-m-d H:i:s');
        //$datos['fecha_iso'] = "ISODate('".date('Y-m-d H:i:s')."')";
        $datos['fecha']      = date('Y-m-d');
        $datos['url']        = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $datos['ip']         = $_SERVER['REMOTE_ADDR'];
        $datos['url_origen'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']
                : '';
        $datos['agente']     = $_SERVER['HTTP_USER_AGENT'];
        $id                  = $this->guardar($datos);
        return $id;
    }

    public function getVisitasByDia($id)
    {

        $keys      = array('fecha' => 1);
        $initial   = array('total' => 0);
        $reduce    = 'function (obj, prev) { prev.total++; }';
        $desde     = date('Y-m-d', strtotime('-30 day'));
        $hasta     = date('Y-m-d');
        $condition = array('condition' => array('aviso.url_id' => $id, 'fecha' =>
                array('$gte' => $desde)));

        $collection = $this->getCollection();
        $res        = $collection->group($keys, $initial, $reduce, $condition);

        return $res;
    }

    public function getVisitasTodales()
    {
        $keys       = array('fecha' => 1);
        $initial    = array('total' => 0);
        $reduce     = 'function (obj, prev) { prev.total++; }';
        $desde      = date('Y-m-d', strtotime('-30 day'));
        $hasta      = date('Y-m-d');
        $condition  = array('condition' => array('fecha' =>
                array('$gte' => $desde)));
        $collection = $this->getCollection();
        $res        = $collection->group($keys, $initial, $reduce, $condition);
        $total=0;
        foreach ($res["retval"] as $key => $value) {
            $total=$total+(int)$value["total"];
        }
        return $total;
    }
}