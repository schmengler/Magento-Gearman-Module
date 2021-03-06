<?php

class ObserverTest extends Ibuildings_Mage_Test_PHPUnit_ControllerTestCase
{
    public function testDispatchTask()
    {
        $ps = explode(PHP_EOL, `ps ax | grep test_worker`);
        $found = false;
        foreach ($ps as $line) {
            if (preg_match('/test_worker\.php/', $line)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->fail(
                'You need to have started test_worker.php from the ' .
                'tests directory'
            );
        }
        $this->assertTrue($found);

        $id         = uniqid();
        $data       = 'This is a string';
        $e          = array();
        $e['queue'] = 'test';
        $e['task']  = array(
            'id' => $id,
            'payload' => $data,
            'callback' => 'http://magento.development.local/index.php'
        );

        Mage::dispatchEvent('gearman_do_async_task', $e);
        sleep(1);
        $log = explode(
            PHP_EOL,
            file_get_contents(LOG_PATH . 'gearman_testing.log')
        );
        for ($i = 0; $i < count($log); ++$i) {
            $res = preg_match(
                '/' . $id . ' \- ' . $data . '/',
                $log[count($log) - 2]
            );
            if (1 == $res) {
                break;
            }
        }
        $this->assertEquals(1, $res);
    }
}