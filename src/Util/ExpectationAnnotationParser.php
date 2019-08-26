<?php


namespace Codeception\Util;


use Codeception\Configuration;
use Codeception\Exception\ParseException;
use Codeception\Test\Cest;
use Codeception\TestInterface;

class ExpectationAnnotationParser
{
    const DEFAULT_EXPECTATIONS_PATH = "tests/_unique_expectations/";
    const PATH_CONFIG_KEY = 'unique_expectations_path';
    /**
     * @var array
     */
    private $config;

    /**
     * ExpectationAnnotationParser constructor.
     *
     * @param array $config
     *
     * @throws \Codeception\Exception\ConfigurationException
     */
    public function __construct($config = [])
    {
        if(empty($config)){
            $config = Configuration::config();
        }
        $this->config = $config;
    }


    /**
     * @param TestInterface|Cest $test
     *
     * @return array
     */
    public function getExpectations(TestInterface $test)
    {
        if(!$test instanceof Cest){
            return [];
        }
        $expectations = Annotation::forMethod($test->getTestClass(), $test->getTestMethod())->fetchAll('expectation');

        return array_map([$this, 'parseExpectation'], $expectations);
    }

    /**
     * @param       $expectationsPath
     * @param array $matches
     *
     * @return string
     */
    function getExpectationFullPath($path)
    {
        $expectationsPath = isset($this->config['paths'][self::PATH_CONFIG_KEY]) ? $this->config['paths'][self::PATH_CONFIG_KEY] : self::DEFAULT_EXPECTATIONS_PATH;

        return codecept_root_dir($expectationsPath . $path);
    }

    /**
     * @param $expectation
     *
     * @return string
     * @throws ParseException
     */
    public function parseExpectation($expectation)
    {
        $matches = [];
        $expectationRegex = '/\(?\"?(?<filePath>[a-zA-Z0-9_]+)(.json)?\"?\)?/';
        preg_match($expectationRegex, $expectation, $matches);

        if (empty($matches)) {
            throw new ParseException("The 'expectation' annotation could not be parsed (found: '$expectation')");
        }

        $expectationPath = $this->getExpectationFullPath("{$matches['filePath']}.json");
        if(!file_exists($expectationPath)){
            throw new ParseException("The expectation at $expectationPath could not be found ");
        }

        return $expectationPath;
    }
}