<?php

/**
 * Description of Util
 *
 * @author Ronald
 */
class App_View_Helper_Compra extends Zend_View_Helper_HtmlElement
{

    public function Compra()
    {
        return $this;
    }

    public function avisoWeb($data)
    {
        switch ($data["codigo"]) {
            case 'ndiaspub':
                return $data['valor'].' días de Publicación en la web.';

                break;
            case 'ndias':
                return $data['valor'].' días de '.$data['destaque'];
                break;

            default:
                break;
        }
        return false;
    }
}