<?php

namespace Phramework\SystemLog;

use \Phramework\Phramework;
use \Phramework\Extensions\StepCallback;

class SystemLogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Phramework
     */
    private $phramework;

    /**
     * @var SystemLog
     */
    private $systemLog;

    /**
     * @var string
     */
    private $step;

    /**
     * @var object
     */
    private $object;

    /**
     * All defined keys for log objects
     */
    protected static $objectKeys = [
        'URI',
        'method',
        'user_id',
        'request_headers',
        'request_params',
        'request_timestamp',
        'response_headers',
        'response_body',
        'response_timestamp',
        'response_status_code',
        'flags',
        'additional_parameters',
        'errors',
        'exception'
    ];

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //force uri
        $_SERVER['REQUEST_URI'] = '/dummy/1/';
        //$_SERVER['REQUEST_URI'] = '/xdummy/10/';
        $_GET['ok'] = true;
        //$_SERVER['REQUEST_URI'] = '/dummy/';

        //Prepare phramework instance
        $this->phramework = \Phramework\SystemLog\APP\Bootstrap::prepare();

        $settings = \Phramework\SystemLog\APP\Bootstrap::getSettings();

        //Create SystemLog object
        $this->systemLog = new SystemLog($settings['system-log']);
    }

    /**
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers Phramework\SystemLog\SystemLog::register
     */
    public function testRegister()
    {
        $this->setUp();

        $additional_parameters = (object)[
            'API' => 'phpunit'
        ];

        $this->systemLog->register($additional_parameters);

        $that = $this;

        //Use a callback to copy $step and $object arguments from ILog to this PHPUnit object
        \Phramework\SystemLog\APP\Log\PHPUnit::setCallback(
            function (
                $step,
                $object
            ) use (
                $that
            ) {
                $that->step = $step;
                $that->object = $object;
            }
        );

        //Invoke phramework (start test)
        $this->phramework->invoke();

        /**
         * Use step and object for PHPUnit tests
         */

        $this->assertSame(
            $this->step,
            StepCallback::STEP_AFTER_CALL_URISTRATEGY,
            'Expect STEP_AFTER_CALL_URISTRATEGY, since we dont expect any exception'
        );

        $this->assertInternalType('object', $this->object);

        $this->assertInternalType(
            'integer',
            $this->object->request_timestamp
        );

        $this->assertInternalType(
            'integer',
            $this->object->response_timestamp
        );

        $this->assertGreaterThanOrEqual(
            $this->object->request_timestamp,
            $this->object->response_timestamp,
            'Response timestamp must be greater or equal to request timestamp'
        );

        //Expect required keys
        foreach (SystemLogTest::$objectKeys as $key) {
            $this->assertObjectHasAttribute(
                $key,
                $this->object,
                'Object must have key' . $key
            );
        }

        $this->assertNull(
            $this->object->errors,
            'Must be null, since we dont expect any exception'
        );

        $this->assertNull(
            $this->object->exception,
            'Must be null, since we dont expect any exception'
        );

        $this->assertObjectHasAttribute(
            'API',
            $this->object->additional_parameters,
            'Check if additional_parameters "API" passed in object'
        );

        $this->assertSame(
            $additional_parameters->API,
            $this->object->additional_parameters->API,
            'Check if value of additional_parameters "API" is set correctly'
        );

        //$this->assertSame(
        //    200,
        //    $this->object->response_status_code,
        //    'Status code MUST be 200, since we dont expect any exception'
        //);
    }

    /**
     * Invoke phramework causing NotFoundException
     * @covers Phramework\SystemLog\SystemLog::register
     */
    public function testRegisterOnException()
    {
        $this->setUp();

        $_SERVER['REQUEST_URI'] = '/dummyNotFound/';

        $additional_parameters = (object)[
            'API' => 'phpunit'
        ];

        $this->systemLog->register($additional_parameters);

        $that = $this;

        //Use a callback to copy $step and $object arguments from ILog to this PHPUnit object
        \Phramework\SystemLog\APP\Log\PHPUnit::setCallback(
            function (
                $step,
                $object
            ) use (
                $that
            ) {
                $that->step = $step;
                $that->object = $object;
            }
        );

        //Invoke phramework (start test)
        $this->phramework->invoke();

        /**
         * Use step and object for PHPUnit tests
         */

        $this->assertSame(
            $this->step,
            StepCallback::STEP_ERROR,
            'Expect STEP_AFTER_CALL_URISTRATEGY, since we dont expect any exception'
        );

        $this->assertInternalType('object', $this->object);

        $this->assertInternalType(
            'integer',
            $this->object->request_timestamp
        );

        $this->assertInternalType(
            'integer',
            $this->object->response_timestamp
        );

        $this->assertGreaterThanOrEqual(
            $this->object->request_timestamp,
            $this->object->response_timestamp,
            'Response timestamp must be greater or equal to request timestamp'
        );
        
        //Expect required keys
        foreach (SystemLogTest::$objectKeys as $key) {
            $this->assertObjectHasAttribute(
                $key,
                $this->object,
                'Object must have key' . $key
            );
        }

        $this->assertNotNull(
            $this->object->errors,
            'Must not be null, since we expect exception'
        );

        $this->assertNotNull(
            $this->object->exception,
            'Must not be null, since we expect exception'
        );

        $this->assertObjectHasAttribute(
            'API',
            $this->object->additional_parameters,
            'Check if additional_parameters "API" passed in object'
        );

        $this->assertSame(
            $additional_parameters->API,
            $this->object->additional_parameters->API,
            'Check if value of additional_parameters "API" is set correctly'
        );

        //$that->>assertEquals(
        //    404,
        //    $this->object->response_status_code,
        //    'Status code MUST be 404, since we expect exception, caused by NotFoundException'
        //);
    }
}
