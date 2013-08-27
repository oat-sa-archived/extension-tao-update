<?php
/**
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; under version 2
* of the License (non-upgradable).
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
* Copyright (c) 2006-2009 (original work) Public Research Centre Henri Tudor (under the project FP6-IST-PALETTE);
*               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
*
*/

namespace OatBox\Controller;

/**
 *
 */
class Request {
    
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';
    const HTTP_HEAD = 'HEAD';
    

    
    /**
     * @var unknown
     */
    protected $parameters = array();
    /**
     * @var unknown
     */
    protected $method;
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function __construct()
    {
        $this->parameters = array_merge($_GET, $_POST);
        $this->secureParameters();
    
        $this->method = $this->defineMethod();
    }
    
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $name
     * @return Ambigous <NULL, multitype:>
     */
    public function getParameter($name)
    {
        return (isset($this->parameters[$name])) ? $this->parameters[$name] : null;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $name
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]);
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return multitype:
     */
    public function getParameters(){
        return $this->parameters;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $name
     */
    public function hasCookie($name)
    {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $name
     * @return unknown
     */
    public function getCookie($name)
    {
        return $_COOKIE[$name];
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return boolean
     */
    public function isGet()
    {
        return $this->getMethod() == self::HTTP_GET;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return boolean
     */
    public function isPost()
    {
        return $this->getMethod() == self::HTTP_POST;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return boolean
     */
    public function isPut()
    {
        return $this->getMethod() == self::HTTP_PUT;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return boolean
     */
    public function isDelete()
    {
        return $this->getMethod() == self::HTTP_DELETE;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return boolean
     */
    public function isHead()
    {
        return $this->getMethod() == self::HTTP_HEAD;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return unknown
     */
    public function getUserAgent()
    {
        return $_SERVER['USER_AGENT'];
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return unknown
     */
    public function getQueryString()
    {
        return $_SERVER['QUERY_STRING'];
    }
    
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return unknown
     */
    public function getRequestIndex(){
        return $_SERVER['PHP_SELF'];
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return unknown
     */
    public function getRequestURI()
    {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return string
     */
    private function defineMethod()
    {
        $methodAsString = $_SERVER['REQUEST_METHOD'];
        	
        switch ($methodAsString)
        {
        	case 'GET':
        	    $method = self::HTTP_GET;
        	    break;
    
        	case 'POST':
        	    $method = self::HTTP_POST;
        	    break;
    
        	case 'PUT':
        	    $method = self::HTTP_PUT;
        	    break;
    
        	case 'DELETE':
        	    $method = self::HTTP_DELETE;
        	    break;
    
        	case 'HEAD':
        	    $method = self::HTTP_HEAD;
        	    break;
        }
        	
        return $method;
    }
    
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param mixed $pValue
     * @return string
     */
    protected function secure($pValue){
        if (!is_numeric($pValue) && !is_array($pValue)) {
            # stripslashes =  Un-quote string
            # htmlentities =  Convert all applicable characters to HTML entities
            $pValue 	= stripslashes(htmlspecialchars($pValue));
        
            # trim =  Strip whitespace (or other characters) from the beginning and end of a string
            $pValue 	= trim($pValue);
            #$pValue	=strip_tags($pValue);
        } else if (is_array($pValue)) {
        # TODO sécuriser chaque champs
        }
        return $pValue;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    protected function secureParameters()
    {
        foreach ($this->parameters as $key => &$param){
            $param = $this->secure($param);
        }
    }

	
}
?>