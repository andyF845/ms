<?php
require_once "./MemoStore.php";

$time = microtime ( true );

$engine = new MemoStore ( $_REQUEST );

$page = new PageBuilder ( PAGE_TEMPLATE_FILE );

$text = $engine->execute ();
$runtime = round ( microtime ( true ) - $time, 4 );
$isCacheActive = $engine->isCacheActive () ? "вкл" : "выкл";
$footer = sprintf ( "Страница сгенерирована за %s с. Кэширование %s.", $runtime, $isCacheActive );

$page->setField ( 'text', $text );
$page->setField ( 'footer', $footer );

echo $page->buildPage ();
?>