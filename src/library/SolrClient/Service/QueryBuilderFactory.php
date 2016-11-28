<?php
class SolrClient_Service_QueryBuilderFactory
{

    public function __construct()
    {
        /* ... */
    }

    /**
     * @return Field Retorna instancia de Field
     */
    public function getField($field=null, $value=null)
    {
        return new SolrClient_Query_Field($field, $value);
    }

    /**
     * @return QueryString Retorna instancia de clase QueryString
     */
    public function getQueryString()
    {
        return new SolrClient_Query_QueryString();
    }

    /**
     * @param  array       $params Convencion de parametros para convertir QueryString
     * @param  String      $cond  Condicion de union para las porciones de queries
     * @param  Query       $query Query ya tratado, al que se desea adicionar mas parametros.
     * @return QueryString Query string valido para solr
     */
    public function buildQuery($params=array(), $cond='AND', $query=null)
    {
        if(!count($params)) throw new \Exception(__CLASS__.'['.__LINE__.']:Array vacio');
        if(!is_array($params)) throw new \Exception(__CLASS__.'['.__LINE__.']:No array');
//        var_dump($params);exit;
        $q = is_null($query)?$this->getQueryString():$query;
        $eq = array(
            'ubicacion' => 'ubigeo_claves',
            'idiomas' => 'idiomas',
            'niveldeestudios' => 'estudios_claves',
            'niveldeOtrosestudios' => 'otros_estudios',
            'tipodecarrera' => 'tipo_carrera_claves',
            'programas' => 'programas_claves',
            'experiencia' => 'experiencia',
            'edad' => 'edad',            
            'sexo' => 'sexo',
            'tags' => 'det_aptitudes',
            'conadis_code'=>'conadis_code'
            );

        foreach ($params as $fn=>$v) {
            if(in_array($fn,  array_keys($eq)) && is_array($v))
            {
                $sq = $this->getQueryString();
                $f = $this->getField($eq[$fn]);
                foreach ($v as $text) 
                {
                    if(in_array($fn,array('experiencia','edad')))
                    {
                        $rang = explode('-', $text);
                        $rang[1] = ($rang[1]=='mas')?'*':$rang[1];
                        $sq->addField($f->setRange($rang[0], $rang[1]), $eq[$fn]);
                    }
                    else
                    {
                        
                        $text = $this->formatValue($fn, $text);
                        $sq->addField($this->getField($eq[$fn],$text ));
                    }
                }
                $sq->setFieldSeparator('OR');
                $q->addSubQuery($sq);            
            }
        }
        if(isset($params['text']))
        {
            $filter = new App_Filter_Slug();
            $texto = mb_strtolower($filter->sanear_string($params['text']));
            $intval = intval($texto);
            $sq = $this->getQueryString();
            if(empty($texto))
            {
                $sq->addField($this->getField('*', "*"));
            }
            else
            {
                $sq->addField($this->getField('puesto', "'$texto'"));
                if(!empty($intval))
                    $sq->addField($this->getField('numdoc', "*$intval*"));
                $sq->addField($this->getField('carrera', "'$texto'"));
                $sq->addField($this->getField('nomape', "'$texto'"));
                $sq->addField($this->getField('presentacion', "'$texto'"));
                $sq->setFieldSeparator('OR');
            }
            $q->addSubQuery($sq);             
        }
        $q->setFieldSeparator($cond);
        return $q;
    }
    private function formatValue($fn,$text)
    {
        if($fn=='idiomas' || $fn=='niveldeestudios' || $fn=='tipodecarrera' || $fn=='programas' || $fn=='ubicacion' || $fn=='niveldeOtrosestudios')
        {
            $text = "*$text*";            
        }
        
        if($fn=='conadis_code' && $text=='1'){
            $text="*0*";
        }
        return $text;
    }
}
