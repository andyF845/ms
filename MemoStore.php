<?php
include './amf/core.php';
include './memo/config.php';

HeaderWriter::headerHTML ();

define ( LIST_TEMPLATE_FILE, './memo/list.htm' );
define ( ITEM_TEMPLATE_FILE, './memo/item.htm' );
define ( PAGE_TEMPLATE_FILE, './memo/page.htm' );

/**
 * MemoStore engine
 */
class MemoStore extends Application {
	//Supported actions
	const ACTION_GET = 'get';
	const ACTION_SET = 'set';
	const ACTION_DEL = 'del';
	const ACTION_NEW = 'new';
	const ACTION_LIST = 'list';
	
	//Server messages
	const ACTION_UNKNOWN	= './memo/msg/action_unknown.htm';
	const UPDATE_OK			= './memo/msg/update_ok.htm';
	const UPDATE_FAIL		= './memo/msg/update_fail.htm';
	const DELETE_OK			= './memo/msg/delete_ok.htm';
	const DELETE_FAIL		= './memo/msg/delete_fail.htm';
	const ITEM_NOT_FOUND	= './memo/msg/item_not_found.htm';
	const LIST_EMPTY		= './memo/msg/list_empty.htm';
	const NEW_ITEM			= './memo/new_item.htm';
	
	//Cache default expire timeout
	const DEFAUL_CACHE_EXPIRE = 1800;
	
	//Count of messages to be stored in db
	const MESSAGE_STORE_COUNT = 10;
	
	//Count of messages to be displayed
	const MESSAGE_DISPLAY_COUNT = 5;
	
	/**
	 * @see Application::initParams()
	 */
	protected $str_memo;
	protected $int_id;
	protected $raw_action = 'list';
	
	// MySQLConnector object
	protected $sql;
	
	// CacheProvider object
	protected $cache;
	
	function __construct($params, $useCache = true) {
		$this->sql = new MySqlConnector ( SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DATA_BASE );
		$this->cache = new MemCacheProvider ();
		$this->cache->active = $useCache;
		parent::__construct ( $params );
	}
	public function execute() {
		switch ($this->raw_action) {
			case $this::ACTION_GET :
				return $this->getMessage ( $this->int_id );
			case $this::ACTION_SET :
				$result = $this->setMessage ( $this->int_id, date ( 'c' ), $this->str_memo, $_SERVER [REMOTE_ADDR] );
				$this->deleteOldMessages();
				return $result;
			case $this::ACTION_DEL :
				return $this->deleteMessage ( $this->int_id );
			case $this::ACTION_NEW :
				return $this->getServerMessage ( $this::NEW_ITEM );
			case $this::ACTION_LIST :
				return $this->listMessages ();
			default :
				return $this->getServerMessage ( $this::ACTION_UNKNOWN );
		}
	}
	public function isCacheActive() {
		return $this->cache->active;
	}
	/**
	 * @see Application::validateString()
	 */
	protected function validateString($name, $value) {
		return $this->sql->escapeString ( $value );
	}
	
	private function getMessage($id) {
		$message = $this->cache->get ( $id );
		if (! $message) {
			$message = $this->sql->getArrayResult ( "SELECT `id`, `memo`, `date` FROM `data` WHERE `id`=$id LIMIT 1;" );
			if ($message) {
				$message = Templater::makeText ( file_get_contents ( ITEM_TEMPLATE_FILE ), $message [0] );
			} else {
				$message = $this->getServerMessage ( $this::ITEM_NOT_FOUND );
			}
			$this->cache->set ( $id, $message, $this::DEFAUL_CACHE_EXPIRE );
		}
		return $message;
	}
	
	private function setMessage($id, $date, $memo, $ip) {
		$this->cache->delete ( $id );
		$this->cache->delete ( 'all' );
		$result = $this->sql->goSQL ( "INSERT INTO `data` VALUES ($id,'$memo','$date','$ip') ON DUPLICATE KEY UPDATE `date`='$date', `memo`='$memo', `ip`='$ip';" );
		return $this->getServerMessage ( $result ? $this::UPDATE_OK : $this::UPDATE_FAIL );
	}
	
	private function listMessages() {
		$messages = $this->cache->get ( 'all' );
		if (! $messages) {
			$messages = $this->sql->getArrayResult ( "SELECT * FROM `data` ORDER BY `id` DESC LIMIT ".self::MESSAGE_DISPLAY_COUNT.";" );
			$messages = Templater::makeTableFromTemplateFile ( LIST_TEMPLATE_FILE, $messages, $this->getServerMessage ( $this::LIST_EMPTY ) );
			$this->cache->set ( 'all', $messages, $this::DEFAUL_CACHE_EXPIRE );
		}
		return $messages;
	}
	
	private function deleteMessage($id) {
		$this->cache->delete ( $id );
		$this->cache->delete ( 'all' );
		$result = $this->sql->goSQL ( "DELETE FROM `data` WHERE `id`=$id LIMIT 1;" );
		return $this->getServerMessage ( $result ? $this::DELETE_OK : $this::DELETE_FAIL );
	}
	
	private function deleteOldMessages() {
		//get last id
		$lastid = $this->sql->getArrayResult( sprintf( 'SELECT `id` FROM `data` ORDER BY `id` DESC LIMIT %d,1;', self::MESSAGE_STORE_COUNT) );
		$lastid = $lastid[0]['id'];
		//delete old records (id <= lastid) 
		return $this->sql->goSQL( sprintf('DELETE FROM `data` WHERE id <= %d;', $lastid) );
	}
	
	private function getServerMessage($fName) {
		if (! $serverMessage = $this->cache->get ( $fName )) {
			if (file_exists ( $fName )) {
				$serverMessage = file_get_contents ( $fName );
				$this->cache->set ( $fName, $serverMessage, $this::DEFAUL_CACHE_EXPIRE );
			} else {
				$serverMessage = false;
			}
		}
		return $serverMessage;
	}
}
?>