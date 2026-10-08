<?php

class DirectoryGuardTest extends PHPUnit_Framework_TestCase
{
    const CGI_BINARY = '/usr/local/bin/php-cgi';

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
        $this->assertTrue(is_executable(self::CGI_BINARY), self::CGI_BINARY . ' must exist for guard checks');
        foreach (self::guardPaths() as $path) {
            $headers = $this->fetchGuardHeaders($path);
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

    /**
     * @param string $path
     * @return array
     */
    private function fetchGuardHeaders($path)
    {
        $cmd = escapeshellarg(self::CGI_BINARY) . ' ' . escapeshellarg($path);
        $output = shell_exec($cmd);
        $this->assertTrue(is_string($output) && $output !== '', $path . ' produced no php-cgi output');

        $parts = preg_split("/\r?\n\r?\n/", $output, 2);
        $this->assertTrue(is_array($parts) && isset($parts[0]) && $parts[0] !== '', $path . ' missing CGI header block');

        $headers = array();
        foreach (preg_split("/\r?\n/", $parts[0]) as $line) {
            if ($line === '') {
                continue;
            }
            if (strpos($line, ':') === false) {
                continue;
            }
            if (stripos($line, 'Status:') === 0) {
                continue;
            }
            $headers[] = $line;
        }

        $this->assertTrue(count($headers) > 0, $path . ' must emit response headers under php-cgi');

        return $headers;
    }
}
