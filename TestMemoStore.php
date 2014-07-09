<?php

include_once "./MemoStore.php";

class TestMemoStore extends TestCase {
	private $data;
	private function executeEngine($params) {
		$memoStore=new MemoStore($params);
		return $memoStore->execute();
	}
	function testSet() {
		$this->data = array ("action"=>"set","id"=>"12345678","memo"=>"test memo");
		$result = $this->executeEngine($this->data);
		$this->assertTrue(strpos($result,"Сообщение успешно сохранено."));		
	}
	function testGet() {
		$this->data['action'] = "get";
		$result = $this->executeEngine($this->data);
		foreach ($this->data as $name=>$value) {
			if ($name<>'action') 	
				$this->assertTrue(strpos($result, $value));
		}
	}
	function testUpdate() {
		$this->data['action'] = "set";
		$this->data['memo'] = "new test memo";
		$result = $this->executeEngine($this->data);
		$this->assertTrue(strpos($result,"Сообщение успешно сохранено."));
	}
	function testGetAfterUpdate() {
		$this->testGet();
	}
	function testDelete() {
		$this->data = array ("action"=>"del","id"=>"12345678");
		$result = $this->executeEngine($this->data);
		$this->assertTrue(strpos($result,'Сообщение удалено!'));
		$this->data = array ("action"=>"get","id"=>"12345678");
		$result = $this->executeEngine($this->data);
		$this->assertTrue(strpos($result,"Сообщение с указанным id не найдено на сервере!"));
	}
}

$test = new TestMemoStore;
$test->switchToHTMLOutput();
$test->run();
echo $test;
?>