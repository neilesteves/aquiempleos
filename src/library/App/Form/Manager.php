<?php

class App_Form_Manager {

    const NEW_INDEX_NAME = 'NEWFORM';

    /**
     * Formulario base
     * 
     * @var Zend_Form
     */
    protected $_baseForm = null;

    /**
     * Formulario base en modo array
     * 
     * @var Zend_Form
     */
    protected $_blankForm = null;

    /**
     * 
     * @var array
     */
    protected $_forms = array();

    /**
     * Prefijo para el nombre de los elementos
     * 
     * @var string
     */
    protected $_prefix = 'manager';
    protected $_defaultValues = array();
    protected $_clearLast = false;
    protected $_cleanPost;
    private $_errorMessage = null;

    const CLASS_FORM_ESTUDIO = 'Application_Form_Paso2EstudioPublicar';
    const CLASS_FORM_EXPERIENCIA = 'Application_Form_Paso2ExperienciaPublicar';
    const CANTIDAD_MIN_ESTUDIO = 1;
    const CANTIDAD_MIN_EXPERIENCIA = 1;
    const MSJ_ERROR_ESTUDIO = 'Debe requerir por lo menos un estudio';
    const MSJ_ERROR_EXPERIENCIA = 'Debe requerir por lo menos una experiencia';

    public function getPrefix() {
        return $this->_prefix;
    }

    /**
     * 
     * @param Zend_Form $baseForm
     * @param string $prefix
     */
    public function __construct(Zend_Form $baseForm, $prefix = 'manager', $clearLast = false) {
        $this->_prefix = $prefix;
        $this->_clearLast = $clearLast;
        $this->setBaseForm($baseForm);
    }

    /**
     * Asigna el formulario base
     * 
     * @param Zend_Form $form
     * @return void
     */
    public function setBaseForm(Zend_Form $form) {
        $blankForm = clone $form;
        $this->_baseForm = $form;
        $this->_blankForm = $blankForm;
    }

    /**
     * Retorna el formulario base
     * 
     * @return Zend_Form
     */
    public function getBaseForm() {
        return $this->_baseForm;
    }

    /**
     * Llena los datos del formulario
     * 
     * @param array $postData
     * @return void
     */
    public function populate(array $postData) {
        $post = $this->_parse($postData);

        foreach ($post as $index => $values) {
            $form = $this->getForm($index);
            $form->populate($values);
        }
    }

    /**
     * Retorna true si todos los formularios son válidos
     * 
     * @param array $postData Datos de la variable $_POST
     * @return bool
     */
    public function isValid(array $postData) {
        $this->_cleanPost = $this->_parse($this->_cleanLastForm($postData));

        $totalValidos = 0;

        foreach ($this->_cleanPost as $index => $values) {
            $form = $this->getForm($index);
            $data = $this->_cleanPost[$index];

            if ($form->isValid($data)) {
                $totalValidos += 1;
            }
           //   $isValid = (count($this->_cleanPost) == $totalValidos) ? TRUE : FALSE;
        }
    
        $isValid = (count($this->_cleanPost) == $totalValidos) ? TRUE : FALSE;
       

        return $isValid;
    }

    /**
     * Limpia el ultimo form si es que está vacío
     * 
     * @param array $post
     */
    protected function _cleanLastForm(array $post) {
        if (count($this->_parse($post)) == 1) {
            return $post;
        }
        $data = array();
        $prefix = $this->getPrefix();

        $maxIndex = 0;
        foreach ($post as $key => $value) {
            if (substr($key, 0, strlen($prefix)) == $prefix) {
                $parts = explode('_', substr($key, strlen($prefix)));                
                $curIndex = (isset($parts[1]) ? $parts[1] : 0);
                $maxIndex = $maxIndex > $curIndex ? $maxIndex : $curIndex;
            }
        }

        $bf = $this->getBaseForm();
        $elementsNoFilled = 0;

        foreach ($post as $key => $value) {
            if (substr($key, 0, strlen($prefix . "_" . $maxIndex)) == $prefix . "_" . $maxIndex) {
                $parts = explode('_', $key);
                $start = strlen($parts[0]) + strlen($parts[1]) + 2;
                $elementName = substr($key, $start);
                $element = $bf->getElement($elementName);
               
                if (get_class($element) == 'Zend_Form_Element_Select' &&
                        $element->getValue() == '') {
                    $optionsKeys = array_keys($element->getMultiOptions());
                    $firstKey = ((bool) count($optionsKeys)) ? $optionsKeys[0] : null;
                    $defaultValue = $firstKey;
                } else {
                    $defaultValue = $element->getValue();
                    if ($value == 0 && get_class($element) == 'App_Form_Element_SelectAttribs') {
                        $defaultValue = 0;
                    }
                }
                 
//        Zend_Debug::dump($value);
//        Zend_Debug::dump($defaultValue);
                if(empty($value))
                    $value = -1;
                if(empty($defaultValue))
                    $defaultValue = -1;
                if ($value == $defaultValue) {
                    $elementsNoFilled++;
                }
               
                
            }
        }


        if (count($bf->getElements()) == $elementsNoFilled) { 
            foreach ($post as $key => $value) {
                if (substr($key, 0, strlen($prefix . "_" . $maxIndex)) == $prefix . "_" . $maxIndex) {
                    unset($post[$key]);
                }
            }
        }

        return $post;
    }

    /**
     * Retorna la colección de formularios administrados
     * 
     * @return array
     */
    public function getForms() {
        return $this->_forms;
    }

    /**
     * Retorna el formulario con el índice indicado en $index
     * Creará un nuevo formulario si el índice no existe.
     * 
     * @param mixed $index
     * @return Zend_Form
     */
    public function getForm($index, $dataValue = null) {
        if (array_key_exists($index, $this->_forms)) {
            return $this->_forms[$index];
        }

        $names = array();
        $blankForm = clone $this->_baseForm;

        foreach ($blankForm->getElements() as $element) {
            $name = $element->getName();
            $elemName = $this->_newName($name, $index);
            $element->setName($elemName);
            $element->setAttrib('id', $elemName);
            if (isset($dataValue[$name])) {
                $element->setValue($dataValue[$name]);
            }
            $names[] = $name;
        }

        $this->_forms[$index] = $blankForm;
        return $blankForm;
    }

    /**
     * Elimina un formulario de la colecciòn administrada por FormManager
     * 
     * @param string $index
     * @return bool
     */
    public function removeForm($index) {
        if (array_key_exists($index, $this->_forms)) {
            unset($this->_forms[$index]);
            return true;
        }

        return false;
    }

    /**
     * Retorna la cantidad de formularios realizando una limpieza previa de las
     * variables en post.
     * 
     * @return int
     */
    public function getCountForms() {
        return count($this->_cleanPost);
    }

    /**
     * Convierte el valor de campos del tipo [prefix]_[index]_[name]
     * a un array asociativo de tipo 
     * array('[index]' => array('[name]' => 'Valor'))
     * 
     * @param array $post
     */
    protected function _parse(array $post) {
        $data = array();
        foreach ($post as $key => $value) {
            $parts = explode('_', $key, 3);
            if (count($parts) == 3 && $parts[0] == $this->_prefix) {
                $index = $parts[1];
                $field = $parts[2];

                $data[$index][$field] = $value;
            }
        }
        return $data;
    }

    /**
     * Genera el nuevo nombre del campo. PREFIX + INDEX + FIELD_NAME
     * 
     * @param string $name
     * @return string
     */
    protected function _newName($name, $index = self::NEW_INDEX_NAME) {
        return sprintf("%s_%s_%s", $this->_prefix, $index, $name);
    }

    /**
     * Retorna el número de formularios dinámicos 
     * 
     * @param array $postData datos de la variable $_POST
     * @return int
     */
    protected function _getFormCount(array $postData) {
        $data = $this->_parse($postData);
        return $count($data);
    }

    public function getCleanPost() {
        return $this->_cleanPost;
    }

    public function getErrorMessage() {
        return $this->_errorMessage;
    }

    public function isEmptyLastForm() {
        $blankForm = end($this->_forms);

        foreach ($blankForm->getElements() as $element) {
            $elemValue = $element->getValue();
            if(!empty($elemValue))
                return false;
        }

        return true;
    }

}
