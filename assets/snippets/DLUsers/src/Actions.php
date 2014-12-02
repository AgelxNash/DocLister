<?php namespace DLUsers;

include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/FS.php');
include_once(MODX_BASE_PATH . 'assets/lib/MODxAPI/modUsers.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLCollection.class.php');

class Actions{
    protected $modx = null;
    public $userObj = null;
    public $url = array();
    protected static $lang = null;
    protected static $langDic = array();
    /**
     * @var cached reference to singleton instance
     */
    protected static $instance;

    /**
     * gets the instance via lazy initialization (created on first usage)
     *
     * @return self
     */
    public static function getInstance(\DocumentParser $modx, $lang, $userClass = 'modUsers', $debug = false)
    {

        if (null === self::$instance) {
            self::$instance = new self($modx, $userClass, $debug);
        }

        self::$lang = $lang;
        self::loadLang($lang);

        return self::$instance;
    }

    /**
     * is not allowed to call from outside: private!
     *
     */
    private function __construct(\DocumentParser $modx, $userClass, $debug)
    {
        $this->modx = $modx;
		$this->userObj = new $userClass($this->modx, $debug);
		$this->url = new \DLCollection($this->modx);

		$site_url = $this->modx->getConfig('site_url');
		$site_start = $this->modx->getConfig('site_start', 1);
		$error_page = $this->modx->getConfig('error_page', $error_page);
		$unauthorized_page = $this->modx->getConfig('unauthorized_page', $error_page);

		$this->config = compact('site_url', 'site_start', 'error_page', 'unauthorized_page');
    }

    /**
     * prevent the instance from being cloned
     *
     * @return void
     */
    private function __clone()
    {

    }

    /**
     * prevent from being unserialized
     *
     * @return void
     */
    private function __wakeup()
    {

    }

    /**
     * Сброс авторизации и обновление страницы
     */
    public function logout($params){
    	$LogoutName = \APIHelpers::getkey($params, 'LogoutName', 'logout');
    	if(is_scalar($LogoutName) && !empty($LogoutName) && isset($_GET[$LogoutName])){
    		$type = 'web';
    		$userID = $this->UserID(compact('type'));
    		if($userID){
    			$this->userObj->edit($userID);
    			if($this->userObj->getID()){
	    			$this->modx->invokeEvent("OnBeforeWebLogout", array(
		    			"userid"   => $this->userObj->getID(),
		    			"username" => $this->userObj->get('username')
		    		));
	    		}
		    	$this->userObj->logOut();
		    	if($this->userObj->getID()){
		    		$this->modx->invokeEvent("OnWebLogout", array(
		    			"userid"        => $this->userObj->getID(),
		    			"username"      => $this->userObj->get('username')
		    		));
		    	}
			    $go = str_replace(
			    	array("?".$LogoutName, "&".$LogoutName),
			    	array("", ""),
			    	$_SERVER['REQUEST_URI']
			    );
			    $start = $this->makeUrl($this->config['site_start']);
			    if($start == $go){
			        $go = $this->config['site_url'];
			    }else{
			        $go = $this->config['site_url'].ltrim($go, '/');
			    }
			    $this->moveTo(array('url' => $go));
    		}else{
    			//Если юзер не авторизован, то показываем ему 404 ошибку
    			$this->modx->sendErrorPage();
    		}

		}
	    return true;
    }

    /**
     * Генерация ссылки под кнопку выход
     * @return string
     */
    public function logoutUrl($params){
    	$LogoutName = \APIHelpers::getkey($params, 'LogoutName', 'logout');
    	$request = parse_url($_SERVER['REQUEST_URI']);

    	//Во избежании XSS мы не сохраняем весь REQUEST_URI, а берем только path
    	/*$query = (!empty($request['query'])) ? $request['query'].'&' : '';*/
    	$query = '?'.$query.$LogoutName;

    	return $request['path'].$query;
    }

    /**
     * Авторизация из блока
     * 		если указан параметр authId, то данные из формы перекидываются в метод AuthPage
     *   	В противном случае вся работа происходит внутри самого блока
     */
    public function AuthBlock($params){
    	$POST = array('backUrl' => $_SERVER['REQUEST_URI']);

    	$dataTPL = array();
    	$error = '';

    	$pwdField = \APIHelpers::getkey($params, 'pwdField', 'password');
		$emailField = \APIHelpers::getkey($params, 'emailField', 'email');
		$rememberField = \APIHelpers::getkey($params, 'rememberField', 'remember');

    	$type = 'web';
		if($this->UserID(compact('type'))){
			$tpl = \APIHelpers::getkey($params, 'tplProfile', '');
			if(empty($tpl)){
				$tpl = $this->getTemplate('tplProfile');
			}
			$dataTPL = $this->userObj->toArray();
			$dataTPL['url.logout'] = $this->logoutUrl($params);
    		$homeID = \APIHelpers::getkey($params, 'homeID');
			if(!empty($homeID)){
				$dataTPL['url.profile'] = $this->makeUrl($homeID);
			}
		}else{
			$tpl = \APIHelpers::getkey($params, 'tplForm', '');
			if(empty($tpl)){
				$tpl = $this->getTemplate('authForm');
			}
			$POST = $this->Auth($pwdField, $emailField, $rememberField, $POST['backUrl'], __METHOD__, $error);
	    	$dataTPL = array(
				'backUrl' => \APIHelpers::getkey($POST, 'backUrl', ''),
				'emailValue' => \APIHelpers::getkey($POST, 'email', ''),
				'emailField' => $emailField,
				'pwdField' => $pwdField,
		    	'method' => strtolower(__METHOD__),
				'error' => $error
			);
			$authId = \APIHelpers::getkey($params, 'authId');
			if(!empty($authId)){
				$dataTPL['authPage'] = $this->makeUrl($authId);
				$dataTPL['method'] = strtolower(__CLASS__ . '::'. 'authpage');
			}
		}
		return \DLTemplate::getInstance($this->modx)->parseChunk($tpl, $dataTPL);
    }

	/**
	 * Авторизация на сайте со страницы авторизации
	 * [!Auth? &login=`password` &pwdField=`password` &homeID=`72`!]
	 */
	public function AuthPage($params){
		$homeID = \APIHelpers::getkey($params, 'homeID');
		$this->isAuthGoHome(array('id' => $homeID));

		$error = '';
		$POST = array('backUrl' => '');

		$pwdField = \APIHelpers::getkey($params, 'pwdField', 'password');
		$emailField = \APIHelpers::getkey($params, 'emailField', 'email');
		$rememberField = \APIHelpers::getkey($params, 'rememberField', 'remember');

		$tpl = \APIHelpers::getkey($params, 'tpl', '');
		if(empty($tpl)){
			$tpl = $this->getTemplate('authForm');
		}

		$request = parse_url($_SERVER['REQUEST_URI']);
		if(!empty($_SERVER['HTTP_REFERER'])){
			/**
			 * Thank you for super protection against hacking in protect.inc.php:-)
			 */
			$refer = htmlspecialchars_decode($_SERVER['HTTP_REFERER'], ENT_QUOTES);
		}else{
			$refer = $request;
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$backUrl = \APIHelpers::getkey($_POST, 'backUrl', $POST['backUrl']);
			if(!is_scalar($backUrl)){
				$backUrl = $refer;
			}else{
				$backUrl = urldecode($backUrl);
			}
		}else{
			$backUrl = $refer;
		}
		$backUrl = parse_url($backUrl);
		if(!empty($backUrl['path']) && $request['path'] != $backUrl['path']){
		    $POST['backUrl'] = $backUrl['path'];
		}else{
			$selfHost = rtrim(str_replace("http://", "", $this->config['site_url']), '/');
			if(empty($uri['host']) || $uri['host']==$selfHost){
				$query = !empty($uri['query']) ? '?'.$uri['query'] : '';
			    $POST['backUrl'] = !empty($uri['path']) ? $uri['path'].$query : '';
			}else{
				$POST['backUrl'] = '';
			}
		}
		if(!empty($POST['backUrl'])){
			$idURL = $this->moveTo(array(
				'url' => '/'.ltrim($POST['backUrl'], '/'),
			    'validate' => true
			));
		}else{
			$idURL = 0;
		}
		if(empty($idURL)){
			if(empty($homeID)){
				$homeID = $this->config['site_start'];
			}
			$POST['backUrl'] = $this->makeUrl($homeID);
		}
		$POST = $this->Auth($pwdField, $emailField, $rememberField, $POST['backUrl'], __METHOD__, $error);
		return \DLTemplate::getInstance($this->modx)->parseChunk($tpl, array(
		    'backUrl' => \APIHelpers::getkey($POST, 'backUrl', ''),
			'emailValue' => \APIHelpers::getkey($POST, 'email', ''),
			'emailField' => $emailField,
		    'pwdField' => $pwdField,
		    'method' => strtolower(__METHOD__),
			'error' => $error
		));
	}
	protected function Auth($pwdField, $emailField, $rememberField, $backUrl, $method, &$error){
		$POST = array(
			'backUrl' => urlencode($backUrl)
		);
		$userObj = &$this->userObj;
		if($_SERVER['REQUEST_METHOD']=='POST' && \APIHelpers::getkey($_POST, 'method', '') == strtolower($method)){
			$POST = array_merge($POST, array(
				'password' => \APIHelpers::getkey($_POST, $pwdField, ''),
				'email' => \APIHelpers::getkey($_POST, $emailField, ''),
				'remember' => (int)\APIHelpers::getkey($_POST, $rememberField, 0)
			));
			if(!empty($POST['email']) && is_scalar($POST['email']) && !$userObj->emailValidate($POST['email'], false)){
				$openUser = $userObj->edit($POST['email']);
				$this->modx->invokeEvent("OnBeforeWebLogin", array(
		            "username"		=> $POST['email'],
		            "userpassword"	=> $POST['password'],
		            "rememberme"	=> $POST['remember'],
		            'userObj'		=> $userObj
		        ));
				if($userObj->getID() !== false && !$userObj->checkBlock($userObj->getID())){
					$pluginFlag = $this->modx->invokeEvent("OnWebAuthentication", array(
	                    "userid"        => $userObj->getID(),
	                    "username"      => $userObj->get('username'),
	                    "userpassword"  => $POST['password'],
	                    "savedpassword" => $userObj->get('password'),
	                    "rememberme"    => $POST['remember'],
	                ));
					if(
						($pluginFlag === true || $userObj->testAuth($userObj->getID(), $POST['password'], 0))
							&&
						$userObj->authUser($userObj->getID(), $POST['remember'])
					){
						$this->modx->invokeEvent("OnWebLogin", array(
			                "userid"		=> $userObj->getID(),
			                "username"		=> $userObj->get('username'),
			                "userpassword"	=> $POST['password'],
			                "rememberme"	=> $POST['remember'],
			            ));
						$this->moveTo(array('url' => urldecode($POST['backUrl'])));
					}else{
						$error = 'error.incorrect_password';
					}
				}else{
					$error = 'error.no_user';
				}
			}else{
				$error = 'error.incorrect_mail';
				$POST['email'] = '';
			}
		}
		if(!empty($error)){
			$error = static::getLangMsg($error, \APIHelpers::getkey($params, $error, ''));
		}
		return $POST;
	}
	/**
	 * Информация о пользователе
	 * [!DLUsers? &action=`UserInfo` &field=`fullname` &id=`2`!]
	 */
	public function UserInfo($params){
		$out = '';
		$type = 'web';
		$userID = \APIHelpers::getkey($params, 'id', 0);
		if(empty($userID)){
			$userID = $this->UserID(compact('type'));
		}
		$field = \APIHelpers::getkey($params, 'field', 'username');
		if($userID > 0){
			$this->userObj->edit($userID);
			switch(true){
				case ($field == $this->userObj->fieldPKName()):{
					$out = $this->userObj->getID();
					break;
				}
				case ($this->userObj->issetField($field)):{
					$out = $this->userObj->get($field);
					break;
				}
			}
		}
		return $out;
	}
	/**
	 * ID пользователя
	 */
	public function UserID($params){
		$type = 'web';
		return $this->modx->getLoginUserID($type);
	}
	/**
	 * Если не авторизован - то отправить на страницу
	 */
	public function isGuestGoHome($params){
		$type = 'web';
		if(!$this->UserID(compact('type'))){
			/**
			 * @see : http://modx.im/blog/triks/105.html
			 */
			$this->modx->invokeEvent('OnPageUnauthorized');
			$id = \APIHelpers::getkey($params, 'id', $this->config['unauthorized_page']);
		    $this->moveTo(compact('id'));
		}
		return;
	}

	/**
	 * Если авторизован - то открыть личный кабинет
	 */
	public function isAuthGoHome($params){
		$type = 'web';
		$userID = $this->UserID(compact('type'));
		if($userID>0){
		    $id = \APIHelpers::getkey($params, 'id');
		    if(empty($id)){
				$id = $this->modx->getConfig('login_home', $this->config['site_start']);
		    }
		    $this->moveTo(compact('id'));
		}
		return;
	}

	/**
	 * Редирект
	 */
	public function moveTo($params){
		$out = '';
		$id = (int)\APIHelpers::getkey($params, 'id', 0);
		$uri = \APIHelpers::getkey($params, 'url', '');
		if((empty($uri) && !empty($id)) || !is_string($uri)){
			$uri = $this->makeUrl($id);
		}
		$code = (int)\APIHelpers::getkey($params, 'code', 0);
		$addUrl = \APIHelpers::getkey($params, 'addUrl', '');
		if(is_scalar($addUrl) && $addUrl!=''){
		    $uri .= "?".$addUrl;
		}
		if(\APIHelpers::getkey($params, 'validate', false)){
			if(isset($this->modx->snippetCache['getPageID'])){
				$out = $this->modx->runSnippet('getPageID', compact('uri'));
				if(empty($out)){
					$uri = '';
				}
			}else{
				$uri = \DLTemplate::getInstance($this->modx)->sanitarTag($uri);
			}
		}else{
			//$modx->sendRedirect($url, 0, 'REDIRECT_HEADER', 'HTTP/1.1 307 Temporary Redirect');
			header("Location: ".$uri, true, ($code>0 ? $code : 307));
		}
		return $uri;
	}

	/**
	 * Создание ссылки на страницу
	 *
	 * @param  int $id ID документа
	 * @return string
	 */
	protected function makeUrl($id = null){
		$id = (int)$id;
		if($id <= 0){
			$id = $this->modx->documentObject['id'];
		}
		if($this->url->containsKey($id)){
			$url = $this->url->get($id);
		}else{
			$url = $this->modx->makeUrl($id);
			$this->url->set($id, $url);
		}
		return $url;
	}
	protected function getTemplate($name){
		$out = '';
		$file = dirname(dirname(__FILE__)).'/tpl/'.$name.'.html';
		if( \Helpers\FS::getInstance()->checkFile($file)){
			$out = '@CODE: '.file_get_contents($file);
		}
		return $out;
	}
	protected static function loadLang($lang){
		$file = dirname(dirname(__FILE__)).'/lang/'.$lang.'.php';
		if( ! \Helpers\FS::getInstance()->checkFile($file)){
			$file = false;
		}
		if(!empty($lang) && !isset(static::$langDic[$lang]) && !empty($file)){
			static::$langDic[$lang] = include_once($file);
			if(is_array(static::$langDic[$lang])){
				static::$langDic[$lang] = \APIHelpers::renameKeyArr(static::$langDic[$lang], $lang);
			}else{
				static::$langDic[$lang] = array();
			}
		}
		return !(empty($lang) || empty(static::$langDic[$lang]));
	}
	protected static function getLangMsg($key, $default){
		$out = $default;
		$lng = static::$lang;
		$dic = static::$langDic;
		if(isset($dic[$lng], $dic[$lng][$lng.'.'.$key])){
			$out = $dic[$lng][$lng.'.'.$key];
		}
		if(class_exists('evoBabel', false) && isset($this->modx->snippetCache['lang'])){
			$msg = $this->modx->runSnippet('lang', array('a' => 'DLUsers.'.$key));
			if(!empty($msg)){
				$out = $msg;
			}
		}
		return $out;
	}
}