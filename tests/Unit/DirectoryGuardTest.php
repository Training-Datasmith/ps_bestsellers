<?php

class DirectoryGuardTest extends PHPUnit_Framework_TestCase
{
    public static $headerChecksSupported = true;
    public static $headerCheckNote = '';

    public static function probeHeaderSupport()
    {
        $root = dirname(dirname(__DIR__));
        $dump = $root . '/tests/Support/dump-headers.php';
        $target = $root . '/index.php';
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($dump) . ' ' . escapeshellarg($target);
        $json = shell_exec($cmd);
        $headers = json_decode(trim($json), true);
        if (!is_array($headers) || count($headers) === 0) {
            self::$headerChecksSupported = false;
            self::$headerCheckNote = 'headers_list() empty under CLI; header assertions skipped';
        }
    }

    public static function setUpBeforeClass()
    {
        self::probeHeaderSupport();
    }

    public static function guardPaths()
    {
        $root = dirname(dirname(__DIR__));

        return array(
            $root . '/index.php',
            $root . '/views/index.php',
            $root . '/views/templates/index.php',
            $root . '/views/templates/hook/index.php',
            $root . '/translations/index.php',
            $root . '/tests/index.php',
            $root . '/tests/phpstan/index.php',
        );
    }

    public function testGuardScripts()
    {
        self::probeHeaderSupport();
        $dump = dirname(dirname(__DIR__)) . '/tests/Support/dump-headers.php';
        foreach (self::guardPaths() as $path) {
            $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($dump) . ' ' . escapeshellarg($path);
            $json = shell_exec($cmd);
            $headers = json_decode(trim($json), true);
            $this->assertTrue(is_array($headers), $path);
            if (!self::$headerChecksSupported) {
                continue;
            }
            $this->assertContains('Expires: Mon, 26 Jul 1997 05:00:00 GMT', $headers, $path);
            $this->assertContains('Cache-Control: no-store, no-cache, must-revalidate', $headers, $path);
            $this->assertContains('Cache-Control: post-check=0, pre-check=0', $headers, $path);
            $this->assertContains('Pragma: no-cache', $headers, $path);
            $this->assertContains('Location: ../', $headers, $path);
            $lastModified = null;
            foreach ($headers as $header) {
                if (strpos($header, 'Last-Modified:') === 0) {
                    $lastModified = substr($header, strlen('Last-Modified: '));
                }
            }
            $this->assertTrue(is_string($lastModified), $path);
            $this->assertRegExp('/^[A-Z][a-z]{2}, \d{2} [A-Z][a-z]{2} \d{4} \d{2}:\d{2}:\d{2} GMT$/', $lastModified, $path);
            $ts = strtotime($lastModified . ' UTC');
            $this->assertTrue(is_int($ts), $path);
            $this->assertTrue(abs(time() - $ts) <= 120, $path);
        }
    }
}
