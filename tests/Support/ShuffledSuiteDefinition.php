<?php

class ShuffledSuiteDefinition
{
    public static function suite()
    {
        $seed = getenv('TEST_SHUFFLE_SEED');
        if ($seed === false || $seed === '') {
            fwrite(STDERR, "TEST_SHUFFLE_SEED is required for shuffled runs\n");
            exit(1);
        }

        $methods = self::collectTestMethods();
        self::shuffleWithSeed($methods, (int) $seed);

        fwrite(STDOUT, "ShuffledSuite seed={$seed} order=" . implode(', ', array_map(function ($m) {
            return $m[0] . '::' . $m[1];
        }, $methods)) . "\n");

        $suite = new PHPUnit_Framework_TestSuite('ShuffledSuite');
        foreach ($methods as $pair) {
            $class = $pair[0];
            $method = $pair[1];
            $suite->addTest(new $class($method));
        }

        return $suite;
    }

    private static function collectTestMethods()
    {
        $methods = array();
        $dir = dirname(__DIR__) . '/Unit';
        foreach (glob($dir . '/*Test.php') as $file) {
            $class = basename($file, '.php');
            require_once $file;
            $ref = new ReflectionClass($class);
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $name = $method->getName();
                if (strpos($name, 'test') === 0) {
                    $methods[] = array($class, $name);
                }
            }
        }

        return $methods;
    }

    private static function shuffleWithSeed(&$methods, $seed)
    {
        mt_srand($seed);
        $n = count($methods);
        for ($i = $n - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $tmp = $methods[$i];
            $methods[$i] = $methods[$j];
            $methods[$j] = $tmp;
        }
    }
}
